<?php

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\UrlCheckService;
use App\Service\UrlService;
use App\Support\CheckViewFormatter;
use Slim\App;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return static function (
    App $app,
    PhpRenderer $renderer,
    Messages $flash,
    UrlRepository $urlRepository,
    UrlCheckRepository $urlCheckRepository,
    CheckViewFormatter $checkViewFormatter,
    UrlService $urlService,
    UrlCheckService $urlCheckService
) {
    $routeParser = $app->getRouteCollector()->getRouteParser();

    $app->get(
        '/',
        function (
            Request $request,
            Response $response
        ) use (
            $renderer,
            $flash,
            $routeParser
        ) {
            return $renderer->render($response, 'index.phtml', [
                'url' => '',
                'errors' => [],
                'flash' => $flash->getMessage('success')[0] ?? null,
                'errorFlash' => $flash->getMessage('error')[0] ?? null,
                'homeUrl' => $routeParser->urlFor('home'),
                'urlsUrl' => $routeParser->urlFor('urls.index'),
            ]);
        }
    )->setName('home');

    $app->post(
        '/urls',
        function (
            Request $request,
            Response $response
        ) use (
            $renderer,
            $flash,
            $routeParser,
            $urlRepository,
            $urlService
        ) {
            $data = $request->getParsedBody();
            $url = trim($data['url'] ?? '');

            $errors = $urlService->validate($url);

            if ($errors !== []) {
                return $renderer->render($response->withStatus(422), 'index.phtml', [
                    'url' => $url,
                    'errors' => $errors,
                    'flash' => null,
                    'errorFlash' => $flash->getMessage('error')[0] ?? null,
                    'homeUrl' => $routeParser->urlFor('home'),
                    'urlsUrl' => $routeParser->urlFor('urls.index'),
                ]);
            }

            $normalizedUrl = $urlService->normalize($url);
            $existingUrl = $urlRepository->findByName($normalizedUrl);

            if ($existingUrl) {
                $flash->addMessage('success', 'Страница уже существует');

                return $response
                    ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $existingUrl['id']]))
                    ->withStatus(302);
            }

            $id = $urlRepository->create($normalizedUrl, date('Y-m-d H:i:s'));

            $flash->addMessage('success', 'Страница успешно добавлена');

            return $response
                ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $id]))
                ->withStatus(302);
        }
    )->setName('urls.store');

    $app->get(
        '/urls',
        function (
            Request $request,
            Response $response
        ) use (
            $renderer,
            $flash,
            $routeParser,
            $urlRepository
        ) {
            $urls = $urlRepository->getAllWithLastCheck();

            return $renderer->render($response, 'urls/index.phtml', [
                'urls' => $urls,
                'flash' => $flash->getMessage('success')[0] ?? null,
                'errorFlash' => $flash->getMessage('error')[0] ?? null,
                'homeUrl' => $routeParser->urlFor('home'),
                'urlsUrl' => $routeParser->urlFor('urls.index'),
            ]);
        }
    )->setName('urls.index');

    $app->get(
        '/urls/{id}',
        function (
            Request $request,
            Response $response,
            array $args
        ) use (
            $renderer,
            $flash,
            $routeParser,
            $urlRepository,
            $urlCheckRepository,
            $checkViewFormatter
        ) {
            $id = (int) $args['id'];

            $url = $urlRepository->findById($id);
            $checks = $urlCheckRepository->findByUrlId($id);
            $formattedChecks = $checkViewFormatter->formatChecks($checks);

            return $renderer->render($response, 'urls/show.phtml', [
                'url' => $url,
                'checks' => $formattedChecks,
                'flash' => $flash->getMessage('success')[0] ?? null,
                'errorFlash' => $flash->getMessage('error')[0] ?? null,
                'homeUrl' => $routeParser->urlFor('home'),
                'urlsUrl' => $routeParser->urlFor('urls.index'),
            ]);
        }
    )->setName('urls.show');

    $app->post(
        '/urls/{id}/checks',
        function (
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
        }
    )->setName('checks.store');
};
