<?php

declare(strict_types=1);

namespace App\Service;

class UrlService
{
    public function validate(string $url): array
    {
        $errors = [];

        if ($url === '') {
            $errors[] = 'URL не должен быть пустым';

            return $errors;
        }

        if (mb_strlen($url) > 255) {
            $errors[] = 'URL превышает 255 символов';

            return $errors;
        }

        $parsedUrl = parse_url($url);

        if ($parsedUrl === false) {
            $errors[] = 'Некорректный URL';

            return $errors;
        }

        $scheme = $parsedUrl['scheme'] ?? null;
        $host = $parsedUrl['host'] ?? null;

        $isValidUrl = filter_var($url, FILTER_VALIDATE_URL) !== false;
        $isValidScheme = in_array($scheme, ['http', 'https'], true);
        $isValidHost = is_string($host)
            && str_contains($host, '.')
            && !str_contains($host, '..')
            && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;

        if (!$isValidUrl || !$isValidScheme || !$isValidHost) {
            $errors[] = 'Некорректный URL';
        }

        return $errors;
    }

    public function normalize(string $url): string
    {
        $parsedUrl = parse_url($url);

        return "{$parsedUrl['scheme']}://{$parsedUrl['host']}";
    }
}
