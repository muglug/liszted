<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class Performance
{
    public const TABLE = 'performance';

    public int $id;
    public ?int $programme;
    public ?string $start;
    public ?string $end;
    public ?int $venue;
    public string $created;
    public ?string $type;
    public ?string $url;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->programme = $row['programme'] !== null ? (int) $row['programme'] : null;
        $entity->start = $row['start'] !== null ? (string) $row['start'] : null;
        $entity->end = $row['end'] !== null ? (string) $row['end'] : null;
        $entity->venue = $row['venue'] !== null ? (int) $row['venue'] : null;
        $entity->created = (string) ($row['created'] ?? '');
        $entity->type = $row['type'] !== null ? (string) $row['type'] : null;
        $entity->url = $row['url'] !== null ? (string) $row['url'] : null;
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `performance` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `performance` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }

    /**
     * @return list<self>
     */
    public static function findByProgramme(int $programmeId): array
    {
        $rows = Connection::fetchAll(
            'SELECT * FROM `performance` WHERE programme = ? ORDER BY start',
            [$programmeId]
        );
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
