<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;
use Liszted\Model\PerformanceModel;

class PerformanceController
{
    public int $id;
    public ?int $venue_id;
    public ?string $start;
    public ?string $end;
    public ?string $url;
    public ?int $accuracy;

    public function __construct(int $id, ?array $entry = null, bool $cache = true)
    {
        $this->id = $id;
        if ($entry === null) {
            $entry = Connection::fetch('SELECT * FROM performance WHERE id = ?', [$id]);
        }
        $this->venue_id = isset($entry['venue']) ? (int) $entry['venue'] : null;
        $this->start = $entry['start'] ?? null;
        $this->end = $entry['end'] ?? null;
        $this->url = $entry['url'] ?? null;
        $this->accuracy = null;
    }

    public function model(): PerformanceModel
    {
        $model = new PerformanceModel();
        $model->id = $this->id;
        if (!empty($this->venue_id)) {
            $model->venue = VenueController::load($this->venue_id);
        }
        $model->start = $this->start;
        $model->end = $this->end;
        $model->url = $this->url;
        $model->accuracy = $this->accuracy;
        return $model;
    }

    /**
     * @param array<string, mixed> $fields
     */
    public static function save(int $id, int $programmeId, array $fields): void
    {
        $start = mktime(
            ((int) $fields['hour'] % 12) + (int) $fields['ampm'],
            (int) $fields['minute'],
            0,
            (int) $fields['month'],
            (int) $fields['day'],
            (int) $fields['year']
        );

        $venueId = null;
        if (!empty($fields['venue'])) {
            $venueId = (int) $fields['venue'];
        } elseif (!empty($fields['venue_city'])) {
            $name = !empty($fields['venue_name']) ? $fields['venue_name'] : null;
            $venueId = (int) VenueController::save(0, [
                'name' => $name,
                'city_name' => $fields['venue_city'],
                'city_state' => $fields['venue_state'] ?? '',
                'city_country' => $fields['venue_country'] ?? '',
            ]);
        }

        $p = [
            'programme' => $programmeId,
            'venue' => $venueId,
            'start' => date("Y-m-d H:i:s", $start),
            'url' => $fields['url'] ?? null,
        ];

        Connection::upsert($id, 'performance', $p);
    }
}
