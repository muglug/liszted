<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class Series
{
    public const TABLE = 'series';

    public int $id;
    public ?string $name;
    public string $created;
    public ?int $user;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->name = $row['name'] !== null ? (string) $row['name'] : null;
        $entity->created = (string) ($row['created'] ?? '');
        $entity->user = $row['user'] !== null ? (int) $row['user'] : null;
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `series` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `series` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
