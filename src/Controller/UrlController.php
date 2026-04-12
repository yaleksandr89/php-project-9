<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\UrlService;
use App\Support\CheckViewFormatter;
use App\Support\ViewDataPreparer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\PhpRenderer;

readonly class UrlController
{
    public function __construct(
        private PhpRenderer $renderer,
        private ViewDataPreparer $viewDataPreparer,
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
        $parsedBody = $request->getParsedBody();
        $data = is_array($parsedBody) ? $parsedBody : [];
        $url = trim((string) ($data['url'] ?? ''));

        $errors = $this->urlService->validate($url);

        if ($errors !== []) {
            return $this->renderer->render(
                $response->withStatus(422),
                'index.phtml',
                $this->viewDataPreparer->prepare([
                    'url' => $url,
                    'errors' => $errors,
                    'flash' => null,
                ])
            );
        }

        $normalizedUrl = $this->urlService->normalize($url);
        $existingUrl = $this->urlRepository->findByName($normalizedUrl);

        if ($existingUrl !== false) {
            $this->flash->addMessage('success', 'Страница уже существует');

            return $response
                ->withHeader(
                    'Location',
                    $this->routeParser->urlFor('urls.show', ['id' => (string) $existingUrl['id']])
                )
                ->withStatus(302);
        }

        $id = $this->urlRepository->create($normalizedUrl, date('Y-m-d H:i:s'));

        $this->flash->addMessage('success', 'Страница успешно добавлена');

        return $response
            ->withHeader(
                'Location',
                $this->routeParser->urlFor('urls.show', ['id' => (string) $id])
            )
            ->withStatus(302);
    }

    public function index(Response $response): Response
    {
        $urls = $this->urlRepository->getAllWithLastCheck();

        return $this->renderer->render(
            $response,
            'urls/index.phtml',
            $this->viewDataPreparer->prepare([
                'urls' => $urls,
            ])
        );
    }

    public function show(Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        $url = $this->urlRepository->findById($id);
        $checks = $this->urlCheckRepository->findByUrlId($id);
        $formattedChecks = $this->checkViewFormatter->formatChecks($checks);

        return $this->renderer->render(
            $response,
            'urls/show.phtml',
            $this->viewDataPreparer->prepare([
                'url' => $url,
                'checks' => $formattedChecks,
            ])
        );
    }
}
