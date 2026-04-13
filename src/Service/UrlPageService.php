<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Support\CheckViewFormatter;
use Slim\Interfaces\RouteParserInterface;

readonly class UrlPageService
{
    public function __construct(
        private UrlRepository $urlRepository,
        private UrlCheckRepository $urlCheckRepository,
        private CheckViewFormatter $checkViewFormatter,
        private RouteParserInterface $routeParser
    ) {
    }

    public function findExistingUrl(string $normalizedUrl): array|false
    {
        return $this->urlRepository->findByName($normalizedUrl);
    }

    public function createUrl(string $normalizedUrl, string $createdAt): int
    {
        return $this->urlRepository->create($normalizedUrl, $createdAt);
    }

    public function getUrlsForIndex(): array
    {
        $urls = $this->urlRepository->getAllWithLastCheck();

        return array_map(function (array $url): array {
            $url['showUrl'] = $this->routeParser->urlFor('urls.show', ['id' => (string) $url['id']]);

            return $url;
        }, $urls);
    }

    public function getUrlPageData(int $id): array|false
    {
        $url = $this->urlRepository->findById($id);

        if ($url === false) {
            return false;
        }

        $checks = $this->urlCheckRepository->findByUrlId($id);
        $formattedChecks = $this->checkViewFormatter->formatChecks($checks);

        return [
            'url' => $url,
            'checks' => $formattedChecks,
            'checkStoreUrl' => $this->routeParser->urlFor('checks.store', ['id' => (string) $id]),
        ];
    }
}
