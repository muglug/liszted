<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;

class ContributionController
{
    public int $id;
    public ?int $contributer_id;
    public ?int $role_id;

    public function __construct(int $id, ?array $entry = null)
    {
        $this->id = $id;
        if ($entry === null) {
            $entry = Connection::fetch('SELECT * FROM contribution WHERE id = ?', [$id]);
        }
        $this->contributer_id = isset($entry['contributer']) ? (int) $entry['contributer'] : null;
        $this->role_id = isset($entry['role']) ? (int) $entry['role'] : null;
    }

    public function editable(): string
    {
        $contributer = new ContributerController($this->contributer_id ?? 0);
        $role = new RoleController($this->role_id ?? 0);
        Form::flush();
        Form::textInput("pc_name_{$this->id}", $contributer->fullName(), "Name");
        Form::textInput("pc_role_{$this->id}", $role->name ?? '', "Role");
        return Form::fieldRow();
    }

    /**
     * @param array<string, mixed> $fields
     */
    public static function save(int $id, int $programmeId, array $fields): void
    {
        if (!empty($fields['name'])) {
            $contributerId = Updater::contributer($fields['name']);
            $roleId = Updater::role($fields['role'] ?? '');
            Connection::upsert($id, 'contribution', [
                'programme' => $programmeId,
                'contributer' => $contributerId,
                'role' => $roleId,
            ]);
        } else {
            Connection::execute('DELETE FROM contribution WHERE id = ?', [$id]);
        }
    }
}
