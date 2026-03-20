<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class Programme
{
    public const TABLE = 'programme';

    public int $id;
    public int $series;
    public ?string $url;
    public string $created;
    public ?int $date_accuracy;
    public int $hidden;
    public ?string $title;
    public int $show_works;
    public ?string $description;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->series = (int) $row['series'];
        $entity->url = $row['url'] !== null ? (string) $row['url'] : null;
        $entity->created = (string) ($row['created'] ?? '');
        $entity->date_accuracy = $row['date_accuracy'] !== null ? (int) $row['date_accuracy'] : null;
        $entity->hidden = (int) ($row['hidden'] ?? 0);
        $entity->title = $row['title'] !== null ? (string) $row['title'] : null;
        $entity->show_works = (int) ($row['show_works'] ?? 0);
        $entity->description = $row['description'] !== null ? (string) $row['description'] : null;
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `programme` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `programme` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
