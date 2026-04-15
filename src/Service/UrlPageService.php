<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Url;
use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Support\CheckViewFormatter;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

readonly class UrlPageService
{
    public function __construct(
        private UrlRepository $urlRepository,
        private UrlCheckRepository $urlCheckRepository,
        private CheckViewFormatter $checkViewFormatter
    ) {
    }

    public function findExistingUrl(string $normalizedUrl): Url|false
    {
        return $this->urlRepository->findByName($normalizedUrl);
    }

    public function createUrl(string $normalizedUrl, string $createdAt): int
    {
        $url = new Url(
            null,
            $normalizedUrl,
            $createdAt
        );

        return $this->urlRepository->create($url);
    }

    public function getUrlsForIndex(): array
    {
        $urls = $this->urlRepository->getAll();
        $urlIds = array_map(static fn(Url $url): int => (int) $url->getId(), $urls);
        $lastChecks = $this->urlCheckRepository->findLastByUrlIds($urlIds);

        return array_map(static function (Url $url) use ($lastChecks): array {
            $urlId = (int) $url->getId();
            $lastCheck = $lastChecks[$urlId] ?? null;

            return [
                'id' => $urlId,
                'name' => $url->getName(),
                'created_at' => $url->getCreatedAt(),
                'status_code' => $lastCheck?->getStatusCode(),
                'last_check_created_at' => $lastCheck?->getCreatedAt(),
            ];
        }, $urls);
    }

    public function getUrlPageData(Request $request, int $id): array
    {
        $url = $this->urlRepository->findById($id);

        if ($url === false) {
            throw new HttpNotFoundException($request);
        }

        $checks = $this->urlCheckRepository->findByUrlId($id);
        $formattedChecks = $this->checkViewFormatter->formatChecks($checks);

        return [
            'url' => [
                'id' => $url->getId(),
                'name' => $url->getName(),
                'created_at' => $url->getCreatedAt(),
            ],
            'checks' => $formattedChecks,
        ];
    }
}
