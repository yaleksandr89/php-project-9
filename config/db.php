<?php

function getPDO(): PDO
{
    $databaseUrl = parse_url(
        getenv('DATABASE_URL')
    );

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
