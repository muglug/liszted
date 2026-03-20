<?php

declare(strict_types=1);

namespace Liszted\Entity;

use Liszted\Database\Connection;

class EpsImport
{
    public const TABLE = 'eps_import';

    public int $id;
    public string $title;
    public string $location;
    public string $city;
    public ?string $country;
    public string $date;
    public string $details;

    private function __construct() {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $entity = new self();
        $entity->id = (int) $row['id'];
        $entity->title = (string) ($row['title'] ?? '');
        $entity->location = (string) ($row['location'] ?? '');
        $entity->city = (string) ($row['city'] ?? '');
        $entity->country = $row['country'] !== null ? (string) $row['country'] : null;
        $entity->date = (string) ($row['date'] ?? '');
        $entity->details = (string) ($row['details'] ?? '');
        return $entity;
    }

    public static function find(int $id): ?self
    {
        $row = Connection::fetch('SELECT * FROM `eps_import` WHERE id = ?', [$id]);
        return $row !== null ? self::fromRow($row) : null;
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM `eps_import` ORDER BY id');
        return array_map(fn(array $row): self => self::fromRow($row), $rows);
    }
}
