<?php

declare(strict_types=1);

namespace App\Support;

use App\Entity\UrlCheck;

class CheckViewFormatter
{
    public function formatChecks(array $checks): array
    {
        return array_map(function (UrlCheck $check): array {
            return [
                'id' => $check->getId(),
                'status_code' => $check->getStatusCode(),
                'h1' => $this->truncate($check->getH1()),
                'title' => $this->truncate($check->getTitle()),
                'description' => $this->truncate($check->getDescription()),
                'created_at' => $check->getCreatedAt(),
            ];
        }, $checks);
    }

    private function truncate(?string $value, int $limit = 200): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return mb_strlen($value) > $limit
            ? mb_substr($value, 0, $limit) . '...'
            : $value;
    }
}
