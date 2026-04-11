<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

return static function (
    $app,
    $renderer,
    $flash,
    $urlRepository,
    $urlCheckRepository,
    $checkViewFormatter
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
            $urlRepository
        ) {
            $data = $request->getParsedBody();
            $url = trim($data['url'] ?? '');

            $errors = [];

            if ($url === '') {
                $errors[] = 'URL не должен быть пустым';
            } elseif (mb_strlen($url) > 255) {
                $errors[] = 'URL превышает 255 символов';
            } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
                $errors[] = 'Некорректный URL';
            }

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

            $parsedUrl = parse_url($url);
            $normalizedUrl = "{$parsedUrl['scheme']}://{$parsedUrl['host']}";

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
            $urlCheckRepository
        ) {
            $urlId = (int) $args['id'];

            $url = $urlRepository->findById($urlId);

            $client = new Client([
                'timeout' => 10,
                'allow_redirects' => true,
                'http_errors' => false,
            ]);

            try {
                $httpResponse = $client->request('GET', $url['name']);
                $statusCode = $httpResponse->getStatusCode();
                $html = (string) $httpResponse->getBody();

                $crawler = new Crawler($html);

                $h1 = $crawler->filter('h1')->count() > 0
                    ? trim($crawler->filter('h1')->first()->text())
                    : null;

                $title = $crawler->filter('title')->count() > 0
                    ? trim($crawler->filter('title')->text())
                    : null;

                $description = $crawler->filter('meta[name="description"]')->count() > 0
                    ? trim((string) $crawler->filter('meta[name="description"]')->first()->attr('content'))
                    : null;

                $urlCheckRepository->create(
                    $urlId,
                    $statusCode,
                    $h1,
                    $title,
                    $description,
                    date('Y-m-d H:i:s')
                );

                $flash->addMessage('success', 'Страница успешно проверена');
            } catch (GuzzleException $e) {
                $flash->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
            }

            return $response
                ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $urlId]))
                ->withStatus(302);
        }
    )->setName('checks.store');
};
