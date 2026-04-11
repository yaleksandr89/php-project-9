<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\ViewDataPreparer;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

readonly class HomeController
{
    public function __construct(
        private PhpRenderer $renderer,
        private ViewDataPreparer $viewDataPreparer
    ) {
    }

    public function index(Response $response): Response
    {
        return $this->renderer->render(
            $response,
            'index.phtml',
            $this->viewDataPreparer->prepare([
                'url' => '',
                'errors' => [],
            ])
        );
    }
}
