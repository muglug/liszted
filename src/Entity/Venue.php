<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class Venue
{
    public const TABLE = 'venue';

    public int $id;
    public ?string $name;
    public ?string $street;
    public ?int $city;
    public ?string $zip;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->name = $row['name'] !== null ? (string) $row['name'] : null;
        $entity->street = $row['street'] !== null ? (string) $row['street'] : null;
        $entity->city = $row['city'] !== null ? (int) $row['city'] : null;
        $entity->zip = $row['zip'] !== null ? (string) $row['zip'] : null;
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `venue` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `venue` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
