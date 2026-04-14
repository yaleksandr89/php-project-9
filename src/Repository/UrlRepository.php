<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Url;
use PDO;

readonly class UrlRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): Url|false
    {
        $stmt = $this->pdo->prepare('SELECT id, name, created_at FROM urls WHERE id = ?');
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return false;
        }

        return $this->hydrate($row);
    }

    public function findByName(string $name): Url|false
    {
        $stmt = $this->pdo->prepare('SELECT id, name, created_at FROM urls WHERE name = ?');
        $stmt->execute([$name]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return false;
        }

        return $this->hydrate($row);
    }

    public function create(Url $url): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO urls (name, created_at) VALUES (?, ?)');
        $stmt->execute([
            $url->getName(),
            $url->getCreatedAt(),
        ]);

        return (int) $this->pdo->lastInsertId('urls_id_seq');
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('
            SELECT id, name, created_at
            FROM urls
            ORDER BY id DESC
        ');

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row): Url => $this->hydrate($row), $rows);
    }

    private function hydrate(array $row): Url
    {
        return new Url(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['created_at']
        );
    }
}
