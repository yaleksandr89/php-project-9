<?php

use App\Controller\HomeController;
use App\Controller\UrlController;
use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\UrlCheckService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;

return static function (
    App $app,
    HomeController $homeController,
    UrlController $urlController,
    UrlCheckService $urlCheckService,
    UrlRepository $urlRepository,
    UrlCheckRepository $urlCheckRepository,
    Messages $flash,
    RouteParserInterface $routeParser
) {
    $app->get('/', function (Request $request, Response $response) use ($homeController) {
        return $homeController->index($response);
    })->setName('home');

    $app->post('/urls', function (Request $request, Response $response) use ($urlController) {
        return $urlController->store($request, $response);
    })->setName('urls.store');

    $app->get('/urls', function (Request $request, Response $response) use ($urlController) {
        return $urlController->index($response);
    })->setName('urls.index');

    $app->get('/urls/{id}', function (Request $request, Response $response, array $args) use ($urlController) {
        return $urlController->show($response, $args);
    })->setName('urls.show');

    $app->post('/urls/{id}/checks', function (
        Request $request,
        Response $response,
        array $args
    ) use (
        $flash,
        $routeParser,
        $urlRepository,
        $urlCheckRepository,
        $urlCheckService
    ) {
        $urlId = (int) $args['id'];
        $url = $urlRepository->findById($urlId);

        $checkResult = $urlCheckService->check($url['name']);

        if ($checkResult['success'] === false) {
            $flash->addMessage('error', $checkResult['error']);

            return $response
                ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $urlId]))
                ->withStatus(302);
        }

        $urlCheckRepository->create(
            $urlId,
            $checkResult['statusCode'],
            $checkResult['h1'],
            $checkResult['title'],
            $checkResult['description'],
            date('Y-m-d H:i:s')
        );

        $flash->addMessage('success', 'Страница успешно проверена');

        return $response
            ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $urlId]))
            ->withStatus(302);
    })->setName('checks.store');
};
