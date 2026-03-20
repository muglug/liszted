<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;

class ProgrammeLineController
{
    public int $id;
    public ?string $text;
    public ?string $url;

    public function __construct(int $id, ?array $entry = null)
    {
        $this->id = $id;
        if ($entry === null) {
            $entry = Connection::fetch('SELECT * FROM programme_line WHERE id = ?', [$id]);
        }
        $this->text = $entry['text'] ?? null;
        $this->url = $entry['url'] ?? null;
    }

    /**
     * @param array<string, mixed> $fields
     */
    public static function save(int $id, int $programmeId, array $fields): void
    {
        if (isset($fields['text'])) {
            if (empty($fields['text'])) {
                Connection::execute('DELETE FROM programme_line WHERE id = ?', [$id]);
            } else {
                Connection::execute('DELETE FROM contribution WHERE programme_line = ?', [$id]);

                if (!empty($fields['composer']) && !empty($fields['role'])) {
                    $contributerId = Updater::contributer($fields['composer']);
                    Connection::insert('contribution', [
                        'contributer' => $contributerId,
                        'role' => (int) $fields['role'],
                        'programme_line' => $id,
                    ]);
                }

                Connection::upsert($id, 'programme_line', [
                    'programme' => $programmeId,
                    'text' => $fields['text'],
                ]);
            }
        } else {
            Connection::upsert($id, 'programme_line', $fields);
        }
    }
}
