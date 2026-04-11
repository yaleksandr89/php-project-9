<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;

class SeoAnalyzer
{
    public function analyze(string $html): array
    {
        $crawler = new Crawler($html);

        $h1 = $crawler->filter('h1')->count() > 0
            ? trim($crawler->filter('h1')->first()->text())
            : null;

        $title = $crawler->filter('title')->count() > 0
            ? trim($crawler->filter('title')->text())
            : null;

        $description = $crawler->filter('meta[name="description"]')->count() > 0
            ? trim((string) $crawler->filter('meta[name="description"]')->first()->attr('content'))
            : null;

        return [
            'h1' => $h1,
            'title' => $title,
            'description' => $description,
        ];
    }
}
