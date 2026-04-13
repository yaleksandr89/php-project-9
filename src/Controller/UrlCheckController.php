<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\UrlCheckService;
use App\Support\WebResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

readonly class UrlCheckController
{
    public function __construct(
        private WebResponder $webResponder,
        private UrlRepository $urlRepository,
        private UrlCheckRepository $urlCheckRepository,
        private UrlCheckService $urlCheckService
    ) {
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $urlId = (int) $args['id'];
        $url = $this->urlRepository->findById($urlId);

        if ($url === false) {
            throw new HttpNotFoundException($request);
        }

        $checkResult = $this->urlCheckService->check($url['name']);

        if ($checkResult['success'] === false) {
            $this->webResponder->addErrorMessage($checkResult['error']);

            return $this->webResponder->redirect(
                $response,
                'urls.show',
                ['id' => (string) $urlId]
            );
        }

        $this->urlCheckRepository->create(
            $urlId,
            $checkResult['statusCode'],
            $checkResult['h1'],
            $checkResult['title'],
            $checkResult['description'],
            date('Y-m-d H:i:s')
        );

        $this->webResponder->addSuccessMessage('Страница успешно проверена');

        return $this->webResponder->redirect(
            $response,
            'urls.show',
            ['id' => (string) $urlId]
        );
    }
}
