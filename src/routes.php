<?php

use App\Controller\HomeController;
use App\Controller\UrlCheckController;
use App\Controller\UrlController;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return static function (
    App $app,
    ContainerInterface $container
) {
    // Главная страница
    $app->get('/', function (Request $request, Response $response) use ($container) {
        return $container->get(HomeController::class)->index($response);
    })->setName('home');

    // Добавление URL
    $app->post('/urls', function (Request $request, Response $response) use ($container) {
        return $container->get(UrlController::class)->store($request, $response);
    })->setName('urls.store');

    // Список URL
    $app->get('/urls', function (Request $request, Response $response) use ($container) {
        return $container->get(UrlController::class)->index($response);
    })->setName('urls.index');

    // Страница URL
    $app->get('/urls/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($container) {
        return $container->get(UrlController::class)->show($request, $response, $args);
    })->setName('urls.show');

    // Запуск проверки URL
    $app->post('/urls/{id:[0-9]+}/checks', function (
        Request $request,
        Response $response,
        array $args
    ) use ($container) {
        return $container->get(UrlCheckController::class)->store($request, $response, $args);
    })->setName('checks.store');
};
