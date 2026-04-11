<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\DomCrawler\Crawler;

return static function ($app, $renderer, $pdo, $flash) {
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
            $pdo,
            $flash,
            $routeParser
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

            $stmt = $pdo->prepare('SELECT id FROM urls WHERE name = ?');
            $stmt->execute([$normalizedUrl]);
            $existingUrl = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($existingUrl) {
                $flash->addMessage('success', 'Страница уже существует');

                return $response
                    ->withHeader('Location', $routeParser->urlFor('urls.show', ['id' => $existingUrl['id']]))
                    ->withStatus(302);
            }

            $stmt = $pdo->prepare('INSERT INTO urls (name, created_at) VALUES (?, ?)');
            $stmt->execute([$normalizedUrl, date('Y-m-d H:i:s')]);

            $id = $pdo->lastInsertId('urls_id_seq');

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
            $pdo,
            $renderer,
            $flash,
            $routeParser
        ) {
            $sql = "
                SELECT 
                    urls.id,
                    urls.name,
                    urls.created_at,
                    url_checks.created_at AS last_check_created_at,
                    url_checks.status_code
                FROM urls
                LEFT JOIN (
                    SELECT DISTINCT ON (url_id)
                        url_id,
                        created_at,
                        status_code
                    FROM url_checks
                    ORDER BY url_id, created_at DESC
                ) AS url_checks ON url_checks.url_id = urls.id
                ORDER BY urls.id DESC
            ";

            $stmt = $pdo->query($sql);
            $urls = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
            $pdo,
            $renderer,
            $flash,
            $routeParser
        ) {
            $id = $args['id'];

            $stmt = $pdo->prepare('SELECT id, name, created_at FROM urls WHERE id = ?');
            $stmt->execute([$id]);
            $url = $stmt->fetch(\PDO::FETCH_ASSOC);

            $checksStmt = $pdo->prepare('
                SELECT id, status_code, h1, title, description, created_at
                FROM url_checks
                WHERE url_id = ?
                ORDER BY id DESC
            ');
            $checksStmt->execute([$id]);
            $checks = $checksStmt->fetchAll(\PDO::FETCH_ASSOC);

            return $renderer->render($response, 'urls/show.phtml', [
                'url' => $url,
                'checks' => $checks,
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
            $pdo,
            $flash,
            $routeParser
        ) {
            $urlId = $args['id'];

            $stmt = $pdo->prepare('SELECT id, name FROM urls WHERE id = ?');
            $stmt->execute([$urlId]);
            $url = $stmt->fetch(\PDO::FETCH_ASSOC);

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

                $stmt = $pdo->prepare(
                    'INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
                 VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $urlId,
                    $statusCode,
                    $h1,
                    $title,
                    $description,
                    date('Y-m-d H:i:s'),
                ]);

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
