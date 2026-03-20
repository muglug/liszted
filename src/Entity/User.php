<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class User
{
    public const TABLE = 'user';

    public int $id;
    public string $name;
    public int $hide;
    public ?string $thumbnail;
    public ?string $hash;
    public string $username;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->name = (string) ($row['name'] ?? '');
        $entity->hide = (int) ($row['hide'] ?? 0);
        $entity->thumbnail = $row['thumbnail'] !== null ? (string) $row['thumbnail'] : null;
        $entity->hash = $row['hash'] !== null ? (string) $row['hash'] : null;
        $entity->username = (string) ($row['username'] ?? '');
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `user` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `user` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }

    public static function findByUsername(string $username): ?self
    {
        $row = Connection::fetch('SELECT * FROM `user` WHERE username = ?', [$username]);
        return $row !== null ? self::fromRow($row) : null;
    }
}
