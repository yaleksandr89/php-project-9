<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use App\Controller\HomeController;
use App\Controller\UrlCheckController;
use App\Controller\UrlController;
use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\SeoAnalyzer;
use App\Service\UrlCheckService;
use App\Service\UrlService;
use App\Support\CheckViewFormatter;
use App\Support\ViewDataPreparer;
use GuzzleHttp\Client;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

session_start();

$pdo = getPDO();
$flash = new Messages();

$urlRepository = new UrlRepository($pdo);
$urlCheckRepository = new UrlCheckRepository($pdo);
$checkViewFormatter = new CheckViewFormatter();

$urlService = new UrlService();
$seoAnalyzer = new SeoAnalyzer();
$httpClient = new Client([
    'timeout' => 10,
    'allow_redirects' => true,
    'http_errors' => false,
]);
$urlCheckService = new UrlCheckService($httpClient, $seoAnalyzer);

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$renderer = new PhpRenderer(__DIR__ . '/../templates');
$renderer->setLayout('layout.phtml');

$routeParser = $app->getRouteCollector()->getRouteParser();
$viewDataPreparer = new ViewDataPreparer($flash, $routeParser);

$homeController = new HomeController($renderer, $viewDataPreparer);
$urlController = new UrlController(
    $renderer,
    $viewDataPreparer,
    $flash,
    $routeParser,
    $urlRepository,
    $urlCheckRepository,
    $checkViewFormatter,
    $urlService
);
$urlCheckController = new UrlCheckController(
    $flash,
    $routeParser,
    $urlRepository,
    $urlCheckRepository,
    $urlCheckService
);

$registerRoutes = require __DIR__ . '/../routes.php';
$registerRoutes(
    $app,
    $homeController,
    $urlController,
    $urlCheckController
);

$app->run();
