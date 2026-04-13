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

    public function getAll(): array
    {
        $stmt = $this->pdo->query('
            SELECT id, name, created_at
            FROM urls
            ORDER BY id DESC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
