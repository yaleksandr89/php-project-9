<?php

declare(strict_types=1);

namespace App\Support;

class CheckViewFormatter
{
    public function formatChecks(array $checks): array
    {
        return array_map(function (array $check): array {
            $check['h1'] = $this->truncate($check['h1'] ?? null);
            $check['title'] = $this->truncate($check['title'] ?? null);
            $check['description'] = $this->truncate($check['description'] ?? null);

            return $check;
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
