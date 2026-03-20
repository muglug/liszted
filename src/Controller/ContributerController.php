<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;

class ContributerController
{
    public int $id;
    public ?string $first_name;
    public ?string $last_name;

    public function __construct(int $id, ?array $entry = null)
    {
        $this->id = $id;
        if ($entry === null) {
            $entry = Connection::fetch('SELECT * FROM contributer WHERE id = ?', [$id]);
        }
        $this->first_name = $entry['first_name'] ?? null;
        $this->last_name = $entry['last_name'] ?? null;
    }

    public function fullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }
}
