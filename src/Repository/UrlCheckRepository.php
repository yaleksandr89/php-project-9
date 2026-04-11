<?php

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
