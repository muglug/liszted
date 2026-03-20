<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class Contribution
{
    public const TABLE = 'contribution';

    public int $id;
    public ?int $contributer;
    public ?int $role;
    public ?int $series;
    public ?int $programme;
    public ?int $programme_line;
    public ?int $performance;
    public string $created;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->contributer = $row['contributer'] !== null ? (int) $row['contributer'] : null;
        $entity->role = $row['role'] !== null ? (int) $row['role'] : null;
        $entity->series = $row['series'] !== null ? (int) $row['series'] : null;
        $entity->programme = $row['programme'] !== null ? (int) $row['programme'] : null;
        $entity->programme_line = $row['programme_line'] !== null ? (int) $row['programme_line'] : null;
        $entity->performance = $row['performance'] !== null ? (int) $row['performance'] : null;
        $entity->created = (string) ($row['created'] ?? '');
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `contribution` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `contribution` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
