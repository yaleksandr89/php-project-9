<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

readonly class UrlRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare('SELECT id, name, created_at FROM urls WHERE id = ?');
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByName(string $name): array|false
    {
        $stmt = $this->pdo->prepare('SELECT id FROM urls WHERE name = ?');
        $stmt->execute([$name]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(string $name, string $createdAt): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO urls (name, created_at) VALUES (?, ?)');
        $stmt->execute([$name, $createdAt]);

        return (int) $this->pdo->lastInsertId('urls_id_seq');
    }

    public function getAllWithLastCheck(): array
    {
        $sql = "
            SELECT 
                urls.id,
                urls.name,
                urls.created_at,
                url_checks.created_at AS last_check_created_at,
                url_checks.status_code
            FROM urls
            LEFT JOIN (
                SELECT DISTINCT ON (url_id)
                    url_id,
                    created_at,
                    status_code
                FROM url_checks
                ORDER BY url_id, created_at DESC
            ) AS url_checks ON url_checks.url_id = urls.id
            ORDER BY urls.id DESC
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
