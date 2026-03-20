<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;
use Liszted\Model\VenueModel;

class VenueController
{
    public int $id;
    public ?string $name;
    public ?int $city_id;
    public ?string $street;
    public ?string $zip;

    /** @var array<string, mixed>|null */
    private ?array $city = null;

    public function __construct(int $id, ?array $entry = null)
    {
        $this->id = $id;
        if ($entry === null) {
            $entry = Connection::fetch('SELECT * FROM venue WHERE id = ?', [$id]);
        }
        $this->name = $entry['name'] ?? null;
        $this->city_id = isset($entry['city']) ? (int) $entry['city'] : null;
        $this->street = $entry['street'] ?? null;
        $this->zip = $entry['zip'] ?? null;
    }

    /**
     * @param int|list<int> $ids
     * @return VenueModel|array<int, VenueModel>
     */
    public static function load(int|array $ids): VenueModel|array
    {
        Constants::init();
        if (is_array($ids)) {
            if (empty($ids)) {
                return [];
            }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $rows = Connection::fetchAll(
                "SELECT venue.id, venue.name, city.name AS city_name, city.state, city.country
                 FROM venue LEFT OUTER JOIN city ON venue.city = city.id
                 WHERE venue.id IN ({$placeholders})",
                $ids
            );
        } else {
            $rows = Connection::fetchAll(
                "SELECT venue.id, venue.name, city.name AS city_name, city.state, city.country
                 FROM venue LEFT OUTER JOIN city ON venue.city = city.id
                 WHERE venue.id = ?",
                [$ids]
            );
        }

        $venues = [];
        foreach ($rows as $s) {
            $venue = new VenueModel();
            $venue->id = (int) $s['id'];
            $venue->name = $s['name'];
            $venue->city = $s['city_name'];
            $venue->state = $s['state'];
            $venue->country = !empty($s['country']) ? (Constants::$countries[$s['country']] ?? '') : '';
            $venues[(int) $s['id']] = $venue;
        }

        if (is_array($ids)) {
            return $venues;
        }
        return array_shift($venues) ?? new VenueModel();
    }

    public function shortName(): string
    {
        Constants::init();
        $venueName = $this->name ?? '';
        if (!empty($this->city_id)) {
            $cityInfo = Connection::fetch('SELECT * FROM city WHERE id = ?', [$this->city_id]);
            if (!empty($cityInfo)) {
                $venueName .= ', ' . $cityInfo['name'];
                if (!empty($cityInfo['country'])) {
                    $venueName .= ', ' . (Constants::$countries[$cityInfo['country']] ?? $cityInfo['country']);
                }
            }
        }
        return $venueName;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function city(): ?array
    {
        if ($this->city === null && !empty($this->city_id)) {
            $this->city = Connection::fetch('SELECT * FROM city WHERE id = ?', [$this->city_id]);
        }
        return $this->city;
    }

    /**
     * @param array<string, mixed> $fields
     */
    public static function save(int $id, array $fields): int|string
    {
        $v = ['name' => $fields['name'] ?? ''];
        if (isset($fields['city_name'])) {
            $cityId = Updater::city($fields['city_name'], $fields['city_state'] ?? '', $fields['city_country'] ?? '');
            $v['city'] = $cityId;
        }
        if (isset($fields['street'])) {
            $v['street'] = $fields['street'];
        }
        if (isset($fields['zip'])) {
            $v['zip'] = $fields['zip'];
        }
        $resolvedId = Updater::venue($v['name'], (int) ($v['city'] ?? 0));
        return Connection::upsert((int) $resolvedId, 'venue', $v);
    }

    /**
     * @return list<array{id: int|string, value: string}>
     */
    public static function search(string $q): array
    {
        $rows = Connection::fetchAll(
            "SELECT *, (SELECT city.name FROM city WHERE city.id = venue.city) AS city_name
             FROM venue WHERE name LIKE ?",
            ["%{$q}%"]
        );
        $results = [];
        foreach ($rows as $row) {
            $results[] = ['id' => $row['id'], 'value' => $row['name'] . ', ' . ($row['city_name'] ?? '')];
        }
        return $results;
    }
}
