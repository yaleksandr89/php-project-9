<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\UrlCheck;
use PDO;

readonly class UrlCheckRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUrlId(int $urlId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT id, url_id, status_code, h1, title, description, created_at
            FROM url_checks
            WHERE url_id = ?
            ORDER BY id DESC
        ');
        $stmt->execute([$urlId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row): UrlCheck => $this->hydrate($row), $rows);
    }

    public function findLastByUrlIds(array $urlIds): array
    {
        if ($urlIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($urlIds), '?'));

        $stmt = $this->pdo->prepare("
            SELECT DISTINCT ON (url_id)
                id,
                url_id,
                status_code,
                h1,
                title,
                description,
                created_at
            FROM url_checks
            WHERE url_id IN ($placeholders)
            ORDER BY url_id, created_at DESC
        ");
        $stmt->execute($urlIds);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($rows as $row) {
            $check = $this->hydrate($row);
            $result[$check->getUrlId()] = $check;
        }

        return $result;
    }

    public function create(UrlCheck $check): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $check->getUrlId(),
            $check->getStatusCode(),
            $check->getH1(),
            $check->getTitle(),
            $check->getDescription(),
            $check->getCreatedAt(),
        ]);
    }

    private function hydrate(array $row): UrlCheck
    {
        return new UrlCheck(
            (int) $row['id'],
            (int) $row['url_id'],
            isset($row['status_code']) ? (int) $row['status_code'] : null,
            $row['h1'] !== null ? (string) $row['h1'] : null,
            $row['title'] !== null ? (string) $row['title'] : null,
            $row['description'] !== null ? (string) $row['description'] : null,
            (string) $row['created_at']
        );
    }
}
