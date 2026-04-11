<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Support\CheckViewFormatter;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

session_start();

$pdo = getPDO();
$flash = new Messages();

$urlRepository = new UrlRepository($pdo);
$urlCheckRepository = new UrlCheckRepository($pdo);
$checkViewFormatter = new CheckViewFormatter();

$app = AppFactory::create();

$renderer = new PhpRenderer(__DIR__ . '/../templates');
$renderer->setLayout('layout.phtml');

$registerRoutes = require __DIR__ . '/../routes.php';
$registerRoutes($app, $renderer, $flash, $urlRepository, $urlCheckRepository, $checkViewFormatter);

$app->run();
