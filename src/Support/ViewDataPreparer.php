<?php

declare(strict_types=1);

namespace App\Support;

use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;

readonly class ViewDataPreparer
{
    public function __construct(
        private Messages $flash,
        private RouteParserInterface $routeParser
    ) {
    }

    public function prepare(array $data = []): array
    {
        return array_merge([
            'flash' => $this->flash->getMessage('success')[0] ?? null,
            'errorFlash' => $this->flash->getMessage('error')[0] ?? null,
            'homeUrl' => $this->routeParser->urlFor('home'),
            'urlsUrl' => $this->routeParser->urlFor('urls.index'),
        ], $data);
    }
}
