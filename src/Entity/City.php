<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class City
{
    public const TABLE = 'city';

    public int $id;
    public string $name;
    public string $state;
    public string $country;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->name = (string) ($row['name'] ?? '');
        $entity->state = (string) ($row['state'] ?? '');
        $entity->country = (string) ($row['country'] ?? '');
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `city` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `city` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
