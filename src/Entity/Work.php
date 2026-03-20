<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class Work
{
    public const TABLE = 'work';

    public int $id;
    public ?string $external_id;
    public ?string $url;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->external_id = $row['external_id'] !== null ? (string) $row['external_id'] : null;
        $entity->url = $row['url'] !== null ? (string) $row['url'] : null;
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `work` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `work` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
