<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class Role
{
    public const TABLE = 'role';

    public int $id;
    public ?string $name;
    public ?string $participle;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->name = $row['name'] !== null ? (string) $row['name'] : null;
        $entity->participle = $row['participle'] !== null ? (string) $row['participle'] : null;
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `role` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `role` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
