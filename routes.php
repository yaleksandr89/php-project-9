<?php

use App\Controller\HomeController;
use App\Controller\UrlCheckController;
use App\Controller\UrlController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return static function (
    App $app,
    HomeController $homeController,
    UrlController $urlController,
    UrlCheckController $urlCheckController
) {
    // Главная страница (форма добавления URL)
    $app->get(
        '/',
        function (
            Request $request,
            Response $response
        ) use ($homeController) {
            return $homeController->index($response);
        }
    )->setName('home');

    // Добавление нового URL
    $app->post(
        '/urls',
        function (
            Request $request,
            Response $response
        ) use ($urlController) {
            return $urlController->store($request, $response);
        }
    )->setName('urls.store');

    // Список всех URL с датой последней проверки
    $app->get(
        '/urls',
        function (Request $request, Response $response) use ($urlController) {
            return $urlController->index($response);
        }
    )->setName('urls.index');

    // Страница конкретного URL и список сделанных проверок
    $app->get(
        '/urls/{id}',
        function (
            Request $request,
            Response $response,
            array $args
        ) use ($urlController) {
            return $urlController->show($response, $args);
        }
    )->setName('urls.show');

    // Запуск проверки и анализа страницы URL
    $app->post(
        '/urls/{id}/checks',
        function (
            Request $request,
            Response $response,
            array $args
        ) use ($urlCheckController) {
            return $urlCheckController->store($response, $args);
        }
    )->setName('checks.store');
};
