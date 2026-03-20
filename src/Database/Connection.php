<?php

declare(strict_types=1);

namespace Liszted\Database;

use PDO;
use PDOException;
use PDOStatement;

class Connection
{
    private static ?PDO $pdo = null;

    /** @var array<string, string> */
    private static array $config = [];

    /**
     * @param array<string, string> $config
     */
    public static function configure(array $config): void
    {
        self::$config = $config;
        self::$pdo = null;
    }

    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            $host = self::$config['host'] ?? getenv('DB_HOST') ?: 'localhost';
            $database = self::$config['database'] ?? getenv('DB_NAME') ?: 'liszted';
            $username = self::$config['username'] ?? getenv('DB_USER') ?: 'liszted';
            $password = self::$config['password'] ?? getenv('DB_PASS') ?: '';

            try {
                self::$pdo = new PDO(
                    "mysql:host={$host};dbname={$database};charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
            }
        }

        return self::$pdo;
    }

    public static function setPdo(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * @param list<mixed> $params
     * @return array<string, mixed>|null
     */
    public static function fetch(string $query, array $params = []): ?array
    {
        $stmt = self::execute($query, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * @param list<mixed> $params
     * @return list<array<string, mixed>>
     */
    public static function fetchAll(string $query, array $params = []): array
    {
        $stmt = self::execute($query, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param list<mixed> $params
     */
    public static function execute(string $query, array $params = []): PDOStatement
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * @param list<mixed> $params
     */
    public static function exec(string $query, array $params = []): int|string
    {
        self::execute($query, $params);
        return self::getPdo()->lastInsertId();
    }

    /**
     * @param array<string, mixed> $fields
     */
    public static function insert(string $table, array $fields): int|string
    {
        $columns = array_keys($fields);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(', ', array_map(fn(string $c): string => "`{$c}`", $columns)),
            implode(', ', $placeholders)
        );

        return self::exec($sql, array_values($fields));
    }

    /**
     * @param array<string, mixed> $fields
     */
    public static function update(string $table, int $id, array $fields): void
    {
        $setClauses = [];
        $params = [];
        foreach ($fields as $column => $value) {
            $setClauses[] = "`{$column}` = ?";
            $params[] = $value;
        }
        $params[] = $id;

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE id = ?',
            $table,
            implode(', ', $setClauses)
        );

        self::execute($sql, $params);
    }

    /**
     * Insert or update: if $id > 0, update; otherwise insert.
     * @param array<string, mixed> $fields
     */
    public static function upsert(int $id, string $table, array $fields): int|string
    {
        if ($id > 0) {
            self::update($table, $id, $fields);
            return $id;
        }
        return self::insert($table, $fields);
    }

    public static function reset(): void
    {
        self::$pdo = null;
        self::$config = [];
    }
}
