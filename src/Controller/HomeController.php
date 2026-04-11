<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\PhpRenderer;

readonly class HomeController
{
    public function __construct(
        private PhpRenderer $renderer,
        private Messages $flash,
        private RouteParserInterface $routeParser
    ) {
    }

    public function index(Response $response): Response
    {
        return $this->renderer->render($response, 'index.phtml', [
            'url' => '',
            'errors' => [],
            'flash' => $this->flash->getMessage('success')[0] ?? null,
            'errorFlash' => $this->flash->getMessage('error')[0] ?? null,
            'homeUrl' => $this->routeParser->urlFor('home'),
            'urlsUrl' => $this->routeParser->urlFor('urls.index'),
        ]);
    }
}
