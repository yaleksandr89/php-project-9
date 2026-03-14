<?php

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

$app = AppFactory::create();

$renderer = new PhpRenderer(__DIR__ . '/../templates');
$renderer->setLayout('layout.phtml');

$app->get('/', function (Request $request, Response $response) use ($renderer) {
    return $renderer->render($response, 'index.phtml');
});

$app->run();
