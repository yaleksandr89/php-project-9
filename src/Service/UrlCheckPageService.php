<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use DateTimeImmutable;

readonly class UrlCheckPageService
{
    public function __construct(
        private UrlRepository $urlRepository,
        private UrlCheckRepository $urlCheckRepository,
        private UrlCheckService $urlCheckService
    ) {
    }

    public function processCheck(int $urlId): array|false
    {
        $url = $this->findUrl($urlId);

        if ($url === false) {
            return false;
        }

        $checkResult = $this->urlCheckService->check($url['name']);

        if ($checkResult['success'] === false) {
            return [
                'success' => false,
                'error' => $checkResult['error'],
            ];
        }

        $this->storeCheck($urlId, $checkResult);

        return [
            'success' => true,
        ];
    }

    private function findUrl(int $urlId): array|false
    {
        return $this->urlRepository->findById($urlId);
    }

    private function storeCheck(int $urlId, array $checkResult): void
    {
        $this->urlCheckRepository->create(
            $urlId,
            $checkResult['status_code'],
            $checkResult['h1'],
            $checkResult['title'],
            $checkResult['description'],
            new DateTimeImmutable()->format('Y-m-d H:i:s')
        );
    }
}
