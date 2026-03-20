<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;
use Liszted\Model\CalendarModel;
use Liszted\Model\ContributerModel;
use Liszted\Model\ProgrammeModel;
use Liszted\Model\VenueModel;
use Liszted\Model\WorkModel;

class ProgrammeController
{
    public int $id;
    public int $series;
    public ?string $series_title;
    public ?string $url;
    public ?string $title;
    public ?int $date_accuracy;
    /** @var list<PerformanceController> */
    private array $performances = [];
    public ?int $show_works;
    public bool $hidden;
    public ?string $description;

    public function __construct(int $id, ?array $entry = null)
    {
        $this->id = $id;
        if ($entry === null) {
            $entry = Connection::fetch('SELECT * FROM programme WHERE id = ?', [$id]);
        }
        $this->series = (int) ($entry['series'] ?? 0);
        $nameQuery = Connection::fetch('SELECT name FROM series WHERE id = ?', [$this->series]);
        $this->series_title = $nameQuery['name'] ?? null;
        $this->url = $entry['url'] ?? null;
        $this->description = $entry['description'] ?? null;
        $this->title = $entry['title'] ?? null;
        $this->date_accuracy = isset($entry['date_accuracy']) ? (int) $entry['date_accuracy'] : null;
        $this->show_works = isset($entry['show_works']) ? (int) $entry['show_works'] : null;
        $this->hidden = ($entry['hidden'] ?? 0) == 1;
    }

    /**
     * @return list<string>
     */
    public function programmeLines(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM programme_line WHERE programme = ?', [$this->id]);
        $lines = [];
        foreach ($rows as $l) {
            $contributerRow = Connection::fetch(
                'SELECT * FROM contributer WHERE id IN (SELECT contributer FROM contribution WHERE programme_line = ?) LIMIT 1',
                [$l['id']]
            );
            $composerName = '';
            if ($contributerRow) {
                $composer = new ContributerController((int) $contributerRow['id'], $contributerRow);
                $composerName = '<strong>' . htmlspecialchars($composer->last_name ?? '', ENT_QUOTES, 'UTF-8') . '</strong> ';
            }
            $lines[] = $composerName . htmlspecialchars($l['text'] ?? '', ENT_QUOTES, 'UTF-8');
        }
        return $lines;
    }

    /**
     * @return list<WorkModel>
     */
    public function getWorkModels(): array
    {
        $rows = Connection::fetchAll('SELECT * FROM programme_line WHERE programme = ?', [$this->id]);
        $programmeLines = [];
        foreach ($rows as $l) {
            $contributerRows = Connection::fetchAll(
                'SELECT contributer.id, contributer.last_name, contributer.first_name,
                        contributer.role_primary, role.name AS role_name
                 FROM contributer
                 LEFT OUTER JOIN contribution ON contribution.programme_line = ? AND contribution.contributer = contributer.id
                 LEFT OUTER JOIN role ON role.id = contribution.role
                 WHERE contributer.id IN (SELECT contributer FROM contribution WHERE programme_line = ?)',
                [$l['id'], $l['id']]
            );
            $contributers = [];
            foreach ($contributerRows as $c) {
                $contributer = new ContributerModel();
                $contributer->first_name = $c['first_name'];
                $contributer->last_name = $c['last_name'];
                $contributer->id = (int) $c['id'];
                $contributer->role = $c['role_name'];
                $contributers[] = $contributer;
            }
            $work = new WorkModel();
            $work->url = $l['url'];
            $work->name = $l['text'];
            $work->contributers = $contributers;
            $programmeLines[] = $work;
        }
        return $programmeLines;
    }

    public function loadPerformances(): void
    {
        $this->performances = [];
        $rows = Connection::fetchAll('SELECT * FROM performance WHERE programme = ? ORDER BY start', [$this->id]);
        foreach ($rows as $s) {
            $this->performances[] = new PerformanceController((int) $s['id'], $s);
        }
    }

    public function model(?int $contributerId = null, ?int $roleId = null): ProgrammeModel
    {
        $programme = new ProgrammeModel();
        $programme->id = $this->id;
        $programme->works = $this->getWorkModels();

        if (empty($this->performances)) {
            $this->loadPerformances();
        }

        $programme->venues = $this->getVenueModels();
        $programme->contributers = $this->getContributerModels($contributerId, $roleId);
        if ($contributerId !== null) {
            $programme->roles = $this->getContributerRoles($contributerId);
        }
        $programme->date_accuracy = $this->date_accuracy;
        $programme->show_works = $this->show_works;
        $programme->dates = $this->getDates();
        $programme->url = $this->url;
        $programme->title = $this->title;
        $programme->description = $this->description;
        $programme->performances = [];

        foreach ($this->performances as $performance) {
            $programme->performances[] = $performance->model();
        }

        return $programme;
    }

    public function addModel(CalendarModel $calendarModel, ?int $contributerId = null, ?int $roleId = null): void
    {
        $programme = $this->model($contributerId, $roleId);

        if (!empty($roleId)) {
            $programme->companies = $this->getRoleContributers($roleId);
        }

        foreach ($programme->venues as $k => $venue) {
            $dates = $this->getVenueDates($k);
            foreach ($dates as $date) {
                $dayProgramme = new ProgrammeModel($programme);
                $dayProgramme->venues = [$venue];
                if (!empty($date[1])) {
                    $dayProgramme->performance_urls[] = $date[1];
                }
                if (!isset($calendarModel->days[$date[0]]) || !is_array($calendarModel->days[$date[0]])) {
                    $calendarModel->days[$date[0]] = [];
                }
                $calendarModel->days[$date[0]][] = $dayProgramme;
            }
        }

        $calendarModel->programmes[] = $programme;
    }

    /**
     * @return list<ContributerModel>
     */
    private function getContributerModels(?int $contributerId = null, ?int $roleId = null): array
    {
        $contributers = [];
        $params = [$this->id, $this->series];
        $excludeSql = '';
        if ($contributerId !== null) {
            $excludeSql = ' AND contribution.contributer != ?';
            $params[] = $contributerId;
        }
        $rows = Connection::fetchAll(
            "SELECT contributer.id AS contributer, contributer.first_name, contributer.last_name,
                    role.name AS role, role.id AS role_id
             FROM contribution
             LEFT OUTER JOIN contributer ON contribution.contributer = contributer.id
             LEFT OUTER JOIN role ON contribution.role = role.id
             WHERE (contribution.programme = ? OR contribution.series = ?){$excludeSql}",
            $params
        );
        foreach ($rows as $p) {
            if ($roleId !== null && (int) ($p['role_id'] ?? 0) === $roleId) {
                continue;
            }
            $contributer = new ContributerModel();
            $contributer->first_name = $p['first_name'];
            $contributer->last_name = $p['last_name'];
            $contributer->id = (int) $p['contributer'];
            $contributer->role = $p['role'];
            $contributers[] = $contributer;
        }
        return $contributers;
    }

    /**
     * @return list<string>
     */
    private function getContributerRoles(int $contributerId): array
    {
        $roles = [];
        $rows = Connection::fetchAll(
            'SELECT role.name AS role
             FROM contribution
             LEFT OUTER JOIN contributer ON contribution.contributer = contributer.id
             LEFT OUTER JOIN role ON contribution.role = role.id
             WHERE (contribution.programme = ? OR contribution.series = ?) AND contributer.id = ?',
            [$this->id, $this->series, $contributerId]
        );
        foreach ($rows as $p) {
            if (!empty($p['role'])) {
                $roles[] = $p['role'];
            }
        }
        return $roles;
    }

    /**
     * @return list<string>
     */
    private function getRoleContributers(int $roleId): array
    {
        $roles = [];
        $rows = Connection::fetchAll(
            'SELECT contributer.first_name, contributer.last_name
             FROM contribution
             LEFT OUTER JOIN contributer ON contribution.contributer = contributer.id
             WHERE (contribution.programme = ? OR contribution.series = ?) AND role = ?',
            [$this->id, $this->series, $roleId]
        );
        foreach ($rows as $p) {
            $roles[] = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
        }
        return $roles;
    }

    /**
     * @return array<int, VenueModel>
     */
    private function getVenueModels(): array
    {
        $venueIds = [];
        foreach ($this->performances as $performance) {
            if (!empty($performance->venue_id)) {
                $venueIds[] = $performance->venue_id;
            }
        }
        if (!empty($venueIds)) {
            $result = VenueController::load($venueIds);
            return is_array($result) ? $result : ($result->id !== null ? [$result->id => $result] : []);
        }
        return [];
    }

    /**
     * @return list<array{string, string|null, int|null}>
     */
    private function getVenueDates(int $venueId): array
    {
        $dates = [];
        $rows = Connection::fetchAll(
            'SELECT start, url, venue FROM performance WHERE venue = ? AND programme = ? ORDER BY start',
            [$venueId, $this->id]
        );
        foreach ($rows as $s) {
            $dates[] = [$s['start'], $s['url'], isset($s['venue']) ? (int) $s['venue'] : null];
        }
        return $dates;
    }

    /**
     * @return list<string>
     */
    private function getDates(): array
    {
        $dates = [];
        foreach ($this->performances as $performance) {
            if ($performance->start !== null) {
                $dates[] = $performance->start;
            }
        }
        return $dates;
    }

    /**
     * @param array<string, mixed> $fields
     */
    public static function save(int $programmeId, array $fields, int $seriesId = 0): void
    {
        $areas = [];
        foreach ($fields as $k => $v) {
            if ($k === 'programme_id') {
                continue;
            }
            $kb = explode('_', $k);
            $kId = array_pop($kb);
            $kS = array_shift($kb);
            $kK = implode('_', $kb);
            if (!isset($areas[$kS])) {
                $areas[$kS] = [];
            }
            if (!isset($areas[$kS][$kId])) {
                $areas[$kS][$kId] = [];
            }
            $areas[$kS][$kId][$kK] = trim((string) $v);
        }

        if (isset($areas['pl'])) {
            foreach ($areas['pl'] as $id => $pl) {
                ProgrammeLineController::save((int) $id, $programmeId, $pl);
            }
        }
        if (isset($areas['pc'])) {
            foreach ($areas['pc'] as $id => $pc) {
                ContributionController::save((int) $id, $programmeId, $pc);
            }
        }
        $performanceIds = [];
        if (isset($areas['pp'])) {
            foreach ($areas['pp'] as $id => $pp) {
                $performanceIds[] = (int) $id;
                PerformanceController::save((int) $id, $programmeId, $pp);
            }
        }

        if (!empty($performanceIds)) {
            $placeholders = implode(', ', array_fill(0, count($performanceIds), '?'));
            Connection::execute(
                "DELETE FROM performance WHERE programme = ? AND id NOT IN ({$placeholders})",
                array_merge([$programmeId], $performanceIds)
            );
        } else {
            Connection::execute('DELETE FROM performance WHERE programme = ?', [$programmeId]);
        }

        $progKey = (string) $programmeId;
        if (isset($areas['p'][$progKey])) {
            $programme = $areas['p'][$progKey];
            $programme['date_accuracy'] = ((int) $programme['date_accuracy']) >= 0 ? (int) $programme['date_accuracy'] : null;
            $programme['hidden'] = !isset($programme['hidden']) ? 1 : 0;
            Connection::update('programme', $programmeId, $programme);
        }

        if (!empty($seriesId)) {
            Connection::update('series', $seriesId, ['user' => UserSession::$id]);
        }
    }
}
