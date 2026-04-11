<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\SeoAnalyzer;
use App\Service\UrlCheckService;
use App\Service\UrlService;
use App\Support\CheckViewFormatter;
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

$renderer = new PhpRenderer(__DIR__ . '/../templates');
$renderer->setLayout('layout.phtml');

$registerRoutes = require __DIR__ . '/../routes.php';
$registerRoutes(
    $app,
    $renderer,
    $flash,
    $urlRepository,
    $urlCheckRepository,
    $checkViewFormatter,
    $urlService,
    $urlCheckService
);

$app->run();
