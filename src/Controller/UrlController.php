<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UrlPageService;
use App\Service\UrlService;
use App\Support\WebResponder;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

readonly class UrlController
{
    public function __construct(
        private WebResponder $webResponder,
        private UrlService $urlService,
        private UrlPageService $urlPageService
    ) {
    }

    public function store(Request $request, Response $response): Response
    {
        $parsedBody = $request->getParsedBody();
        $data = is_array($parsedBody) ? $parsedBody : [];
        $url = trim((string) ($data['url'] ?? ''));

        $errors = $this->urlService->validate($url);

        if ($errors !== []) {
            return $this->webResponder->render(
                $response->withStatus(422),
                'index.phtml',
                [
                    'url' => $url,
                    'errors' => $errors,
                    'flash' => null,
                ]
            );
        }

        $normalizedUrl = $this->urlService->normalize($url);
        $existingUrl = $this->urlPageService->findExistingUrl($normalizedUrl);

        if ($existingUrl !== false) {
            $this->webResponder->addSuccessMessage('Страница уже существует');

            return $this->webResponder->redirect(
                $response,
                'urls.show',
                ['id' => (string) $existingUrl->getId()]
            );
        }

        $id = $this->urlPageService->createUrl(
            $normalizedUrl,
            new DateTimeImmutable()->format('Y-m-d H:i:s')
        );

        $this->webResponder->addSuccessMessage('Страница успешно добавлена');

        return $this->webResponder->redirect(
            $response,
            'urls.show',
            ['id' => (string) $id]
        );
    }

    public function index(Response $response): Response
    {
        return $this->webResponder->render(
            $response,
            'urls/index.phtml',
            [
                'urls' => $this->urlPageService->getUrlsForIndex(),
            ]
        );
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $pageData = $this->urlPageService->getUrlPageData($id);

        if ($pageData === false) {
            throw new HttpNotFoundException($request);
        }

        return $this->webResponder->render(
            $response,
            'urls/show.phtml',
            $pageData
        );
    }
}
