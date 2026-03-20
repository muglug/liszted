<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class Contributer
{
    public const TABLE = 'contributer';

    public int $id;
    public ?string $first_name;
    public ?string $last_name;
    public ?int $role_primary;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->first_name = $row['first_name'] !== null ? (string) $row['first_name'] : null;
        $entity->last_name = $row['last_name'] !== null ? (string) $row['last_name'] : null;
        $entity->role_primary = $row['role_primary'] !== null ? (int) $row['role_primary'] : null;
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `contributer` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `contributer` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
