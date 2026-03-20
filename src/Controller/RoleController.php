<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;

class RoleController
{
    public int $id;
    public ?string $name;

    public function __construct(int $id, ?array $entry = null)
    {
        $this->id = $id;
        if ($entry === null) {
            $entry = Connection::fetch('SELECT * FROM role WHERE id = ?', [$id]);
        }
        $this->name = $entry['name'] ?? null;
    }

    public static function search(string $name): ?int
    {
        $r = Connection::fetch('SELECT id FROM role WHERE name = ?', [$name]);
        return $r !== null ? (int) $r['id'] : null;
    }
}
