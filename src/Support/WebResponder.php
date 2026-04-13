<?php

declare(strict_types=1);

namespace App\Support;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\PhpRenderer;

readonly class WebResponder
{
    public function __construct(
        private PhpRenderer $renderer,
        private ViewDataPreparer $viewDataPreparer,
        private Messages $flash,
        private RouteParserInterface $routeParser
    ) {
    }

    public function render(Response $response, string $template, array $data = []): Response
    {
        return $this->renderer->render(
            $response,
            $template,
            $this->viewDataPreparer->prepare($data)
        );
    }

    public function redirect(Response $response, string $routeName, array $routeParams = []): Response
    {
        return $response
            ->withHeader('Location', $this->routeParser->urlFor($routeName, $routeParams))
            ->withStatus(302);
    }

    public function addSuccessMessage(string $message): void
    {
        $this->flash->addMessage('success', $message);
    }

    public function addErrorMessage(string $message): void
    {
        $this->flash->addMessage('error', $message);
    }
}
