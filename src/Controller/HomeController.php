<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\WebResponder;
use Psr\Http\Message\ResponseInterface as Response;

readonly class HomeController
{
    public function __construct(private WebResponder $webResponder)
    {
    }

    public function index(Response $response): Response
    {
        return $this->webResponder->render(
            $response,
            'index.phtml',
            [
                'url' => '',
                'errors' => [],
            ]
        );
    }
}
