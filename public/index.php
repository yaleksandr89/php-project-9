<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

session_start();

$pdo = getPDO();
$flash = new Messages();

$app = AppFactory::create();

$renderer = new PhpRenderer(__DIR__ . '/../templates');
$renderer->setLayout('layout.phtml');

$registerRoutes = require __DIR__ . '/../routes.php';
$registerRoutes($app, $renderer, $pdo, $flash);

$app->run();
