<?php

declare(strict_types=1);

function getPDO(): PDO
{
    $databaseUrlString = getenv('DATABASE_URL');

    if ($databaseUrlString === false) {
        throw new RuntimeException('DATABASE_URL is not set');
    }

    $databaseUrl = parse_url($databaseUrlString);

    if ($databaseUrl === false) {
        throw new RuntimeException('Invalid DATABASE_URL');
    }

    $host = $databaseUrl['host'] ?? 'localhost';
    $port = $databaseUrl['port'] ?? 5432;
    $dbName = isset($databaseUrl['path']) ? ltrim($databaseUrl['path'], '/') : '';
    $username = $databaseUrl['user'] ?? '';
    $password = $databaseUrl['pass'] ?? '';

    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$dbName}",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}
