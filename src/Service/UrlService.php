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
        } elseif (mb_strlen($url) > 255) {
            $errors[] = 'URL превышает 255 символов';
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
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
