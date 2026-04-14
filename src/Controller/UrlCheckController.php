<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UrlCheckPageService;
use App\Support\WebResponder;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

readonly class UrlCheckController
{
    public function __construct(
        private WebResponder $webResponder,
        private UrlCheckPageService $urlCheckPageService
    ) {
    }

    public function store(Request $request, Response $response, array $args): Response
    {
        $urlId = (int) $args['id'];
        $result = $this->urlCheckPageService->processCheck($urlId);

        if ($result === false) {
            throw new HttpNotFoundException($request);
        }

        if ($result['success'] === false) {
            $this->webResponder->addErrorMessage($result['error']);

            return $this->webResponder->redirect(
                $response,
                'urls.show',
                ['id' => (string) $urlId]
            );
        }

        $this->webResponder->addSuccessMessage('Страница успешно проверена');

        return $this->webResponder->redirect(
            $response,
            'urls.show',
            ['id' => (string) $urlId]
        );
    }
}
