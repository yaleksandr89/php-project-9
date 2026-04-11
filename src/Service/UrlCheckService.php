<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

readonly class UrlCheckService
{
    public function __construct(
        private Client $client,
        private SeoAnalyzer $seoAnalyzer
    ) {
    }

    public function check(string $url): array
    {
        try {
            $httpResponse = $this->client->request('GET', $url);
            $statusCode = $httpResponse->getStatusCode();
            $html = (string) $httpResponse->getBody();

            $seoData = $this->seoAnalyzer->analyze($html);

            return [
                'success' => true,
                'statusCode' => $statusCode,
                'h1' => $seoData['h1'],
                'title' => $seoData['title'],
                'description' => $seoData['description'],
            ];
        } catch (GuzzleException $e) {
            return [
                'success' => false,
                'error' => 'Произошла ошибка при проверке, не удалось подключиться',
            ];
        }
    }
}
