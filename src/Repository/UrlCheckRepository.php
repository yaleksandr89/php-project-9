<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

readonly class UrlCheckRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findByUrlId(int $urlId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT id, status_code, h1, title, description, created_at
            FROM url_checks
            WHERE url_id = ?
            ORDER BY id DESC
        ');
        $stmt->execute([$urlId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findLastByUrlIds(array $urlIds): array
    {
        if ($urlIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($urlIds), '?'));

        $stmt = $this->pdo->prepare("
            SELECT DISTINCT ON (url_id)
                url_id,
                status_code,
                created_at
            FROM url_checks
            WHERE url_id IN ($placeholders)
            ORDER BY url_id, created_at DESC
        ");
        $stmt->execute($urlIds);

        $checks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];

        foreach ($checks as $check) {
            $result[(int) $check['url_id']] = $check;
        }

        return $result;
    }

    public function create(
        int $urlId,
        ?int $statusCode,
        ?string $h1,
        ?string $title,
        ?string $description,
        string $createdAt
    ): void {
        $stmt = $this->pdo->prepare('
            INSERT INTO url_checks (url_id, status_code, h1, title, description, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $urlId,
            $statusCode,
            $h1,
            $title,
            $description,
            $createdAt,
        ]);
    }
}
