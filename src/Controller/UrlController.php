<?php

namespace App\Controller;

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\UrlService;
use App\Support\CheckViewFormatter;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\PhpRenderer;

readonly class UrlController
{
    public function __construct(
        private PhpRenderer $renderer,
        private Messages $flash,
        private RouteParserInterface $routeParser,
        private UrlRepository $urlRepository,
        private UrlCheckRepository $urlCheckRepository,
        private CheckViewFormatter $checkViewFormatter,
        private UrlService $urlService
    ) {
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $url = trim($data['url'] ?? '');

        $errors = $this->urlService->validate($url);

        if ($errors !== []) {
            return $this->renderer->render($response->withStatus(422), 'index.phtml', [
                'url' => $url,
                'errors' => $errors,
                'flash' => null,
                'errorFlash' => $this->flash->getMessage('error')[0] ?? null,
                'homeUrl' => $this->routeParser->urlFor('home'),
                'urlsUrl' => $this->routeParser->urlFor('urls.index'),
            ]);
        }

        $normalizedUrl = $this->urlService->normalize($url);
        $existingUrl = $this->urlRepository->findByName($normalizedUrl);

        if ($existingUrl) {
            $this->flash->addMessage('success', 'Страница уже существует');

            return $response
                ->withHeader('Location', $this->routeParser->urlFor('urls.show', ['id' => $existingUrl['id']]))
                ->withStatus(302);
        }

        $id = $this->urlRepository->create($normalizedUrl, date('Y-m-d H:i:s'));

        $this->flash->addMessage('success', 'Страница успешно добавлена');

        return $response
            ->withHeader('Location', $this->routeParser->urlFor('urls.show', ['id' => $id]))
            ->withStatus(302);
    }

    public function index(Response $response): Response
    {
        $urls = $this->urlRepository->getAllWithLastCheck();

        return $this->renderer->render($response, 'urls/index.phtml', [
            'urls' => $urls,
            'flash' => $this->flash->getMessage('success')[0] ?? null,
            'errorFlash' => $this->flash->getMessage('error')[0] ?? null,
            'homeUrl' => $this->routeParser->urlFor('home'),
            'urlsUrl' => $this->routeParser->urlFor('urls.index'),
        ]);
    }

    public function show(Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        $url = $this->urlRepository->findById($id);
        $checks = $this->urlCheckRepository->findByUrlId($id);
        $formattedChecks = $this->checkViewFormatter->formatChecks($checks);

        return $this->renderer->render($response, 'urls/show.phtml', [
            'url' => $url,
            'checks' => $formattedChecks,
            'flash' => $this->flash->getMessage('success')[0] ?? null,
            'errorFlash' => $this->flash->getMessage('error')[0] ?? null,
            'homeUrl' => $this->routeParser->urlFor('home'),
            'urlsUrl' => $this->routeParser->urlFor('urls.index'),
        ]);
    }
}
