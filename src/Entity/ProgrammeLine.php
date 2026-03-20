<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class ProgrammeLine
{
    public const TABLE = 'programme_line';

    public int $id;
    public ?int $programme;
    public ?string $text;
    public ?int $work;
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
        $entity->text = $row['text'] !== null ? (string) $row['text'] : null;
        $entity->work = $row['work'] !== null ? (int) $row['work'] : null;
        $entity->url = $row['url'] !== null ? (string) $row['url'] : null;
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `programme_line` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `programme_line` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }

    /**
     * @return list<self>
     */
    public static function findByProgramme(int $programmeId): array
    {
        $rows = Connection::fetchAll(
            'SELECT * FROM `programme_line` WHERE programme = ? ORDER BY id',
            [$programmeId]
        );
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
