<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return static function ($app, $renderer, $pdo, $flash) {
    $routeParser = $app->getRouteCollector()->getRouteParser();

    $app->get('/', function (Request $request, Response $response) use ($renderer, $flash, $routeParser) {
        return $renderer->render($response, 'index.phtml', [
            'url' => '',
            'errors' => [],
            'flash' => $flash->getMessage('success')[0] ?? null,
            'homeUrl' => $routeParser->urlFor('home'),
        ]);
    })->setName('home');

    $app->post('/urls', function (Request $request, Response $response) use ($renderer, $pdo, $flash, $routeParser) {
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
                'homeUrl' => $routeParser->urlFor('home'),
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
    })->setName('urls.store');

    $app->get('/urls', function (Request $request, Response $response) use ($pdo, $renderer, $flash, $routeParser) {
        $stmt = $pdo->query('SELECT id, name, created_at FROM urls ORDER BY created_at DESC');
        $urls = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $renderer->render($response, 'urls/index.phtml', [
            'urls' => $urls,
            'flash' => $flash->getMessage('success')[0] ?? null,
            'homeUrl' => $routeParser->urlFor('home'),
        ]);
    })->setName('urls.index');

    $app->get('/urls/{id}', function (Request $request, Response $response, array $args) use ($pdo, $renderer, $flash, $routeParser) {
        $id = $args['id'];

        $stmt = $pdo->prepare('SELECT id, name, created_at FROM urls WHERE id = ?');
        $stmt->execute([$id]);
        $url = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $renderer->render($response, 'urls/show.phtml', [
            'url' => $url,
            'flash' => $flash->getMessage('success')[0] ?? null,
            'homeUrl' => $routeParser->urlFor('home'),
        ]);
    })->setName('urls.show');
};
