<?php

namespace App\Controller;

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\UrlCheckService;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;

readonly class UrlCheckController
{
    public function __construct(
        private Messages $flash,
        private RouteParserInterface $routeParser,
        private UrlRepository $urlRepository,
        private UrlCheckRepository $urlCheckRepository,
        private UrlCheckService $urlCheckService
    ) {
    }

    public function store(Response $response, array $args): Response
    {
        $urlId = (int) $args['id'];
        $url = $this->urlRepository->findById($urlId);

        $checkResult = $this->urlCheckService->check($url['name']);

        if ($checkResult['success'] === false) {
            $this->flash->addMessage('error', $checkResult['error']);

            return $response
                ->withHeader('Location', $this->routeParser->urlFor('urls.show', ['id' => $urlId]))
                ->withStatus(302);
        }

        $this->urlCheckRepository->create(
            $urlId,
            $checkResult['statusCode'],
            $checkResult['h1'],
            $checkResult['title'],
            $checkResult['description'],
            date('Y-m-d H:i:s')
        );

        $this->flash->addMessage('success', 'Страница успешно проверена');

        return $response
            ->withHeader('Location', $this->routeParser->urlFor('urls.show', ['id' => $urlId]))
            ->withStatus(302);
    }
}
