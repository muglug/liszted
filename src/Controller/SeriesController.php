<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;

class SeriesController
{
    public int $id;
    public ?string $name;

    public function __construct(int $id, ?array $entry = null)
    {
        $this->id = $id;
        if ($entry === null) {
            $entry = Connection::fetch('SELECT * FROM series WHERE id = ?', [$id]);
        }
        $this->name = $entry['name'] ?? null;
    }
}
