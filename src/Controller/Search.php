<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;

class Search
{
    public static function contributer(string $firstName, string $lastName = ""): int
    {
        if (!empty($lastName)) {
            $row = Connection::fetch(
                "SELECT id FROM contributer WHERE (first_name = ? AND last_name = ?) OR CONCAT(first_name, ' ', last_name) = ?",
                [$firstName, $lastName, "{$firstName} {$lastName}"]
            );
        } else {
            $row = Connection::fetch(
                "SELECT id FROM contributer WHERE (first_name = ? AND (last_name IS NULL OR last_name = '')) OR CONCAT(first_name, ' ', last_name) = ?",
                [$firstName, $firstName]
            );
        }
        return $row !== null ? (int) $row['id'] : -1;
    }

    public static function user(string $username): int
    {
        $row = Connection::fetch("SELECT id FROM user WHERE name = ?", [$username]);
        return $row !== null ? (int) $row['id'] : -1;
    }

    /**
     * @return list<array{int, string}>
     */
    public static function roles(): array
    {
        $rows = Connection::fetchAll("SELECT id, participle FROM role WHERE participle IS NOT NULL");
        $roles = [];
        foreach ($rows as $r) {
            $roles[] = [(int) $r['id'], $r['participle']];
        }
        return $roles;
    }

    /**
     * @return list<string>
     */
    public static function contributers(): array
    {
        $rows = Connection::fetchAll("SELECT * FROM contributer WHERE role_primary IS NOT NULL");
        $composers = [];
        foreach ($rows as $c) {
            $composers[] = $c['first_name'] . ' ' . $c['last_name'];
        }
        return $composers;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function programmesByContributer(int $contributerId, int $limit = 1000, bool $futureOnly = false): array
    {
        $yesterday = date("Y-m-d", time() - 3600 * 24);

        $sql = "
        SELECT programme.*, MIN(perf.start) AS start
        FROM programme
        INNER JOIN performance perf ON perf.programme = programme.id
        WHERE programme.hidden = 0
          AND programme.id IN (
            SELECT perf2.programme FROM performance perf2
            INNER JOIN contribution c1 ON c1.performance = perf2.id AND c1.contributer = ?

            UNION

            SELECT c2.programme FROM contribution c2
            WHERE c2.contributer = ? AND c2.programme IS NOT NULL

            UNION

            SELECT p2.id FROM programme p2
            INNER JOIN contribution c3 ON c3.series = p2.series AND c3.contributer = ?

            UNION

            SELECT pl.programme FROM programme_line pl
            INNER JOIN contribution c4 ON c4.programme_line = pl.id AND c4.contributer = ?
          )";

        $params = [$contributerId, $contributerId, $contributerId, $contributerId];

        $sql .= " GROUP BY programme.id";

        if ($futureOnly) {
            $sql .= " HAVING MAX(perf.start) > ?";
            $params[] = $yesterday;
        }

        $sql .= " ORDER BY start LIMIT ?";
        $params[] = $limit;

        return Connection::fetchAll($sql, $params);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function programmesByUser(int $userId, int $limit = 1000, bool $futureOnly = false): array
    {
        $yesterday = date("Y-m-d", time() - 3600 * 24);

        $sql = "SELECT programme.*, MIN(perf.start) AS start
        FROM programme
        INNER JOIN series ON series.id = programme.series
        INNER JOIN performance perf ON perf.programme = programme.id
        WHERE programme.hidden = 0
          AND series.user = ?";

        $params = [$userId];

        $sql .= " GROUP BY programme.id";

        if ($futureOnly) {
            $sql .= " HAVING MAX(perf.start) > ?";
            $params[] = $yesterday;
        }

        $sql .= " ORDER BY start LIMIT ?";
        $params[] = $limit;

        return Connection::fetchAll($sql, $params);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function programmesFeaturing(int $contributerId, string $work, int $limit = 1000, bool $futureOnly = false): array
    {
        $yesterday = date("Y-m-d", time() - 3600 * 24);

        $sql = "
        SELECT programme.*, MIN(perf.start) AS start
        FROM programme
        INNER JOIN programme_line pl ON pl.programme = programme.id
        INNER JOIN contribution c ON c.programme_line = pl.id AND c.contributer = ?
        INNER JOIN performance perf ON perf.programme = programme.id
        WHERE programme.hidden = 0
          AND pl.text LIKE ?";

        $params = [$contributerId, $work];

        $sql .= " GROUP BY programme.id";

        if ($futureOnly) {
            $sql .= " HAVING MAX(perf.start) > ?";
            $params[] = $yesterday;
        }

        $sql .= " ORDER BY start LIMIT ?";
        $params[] = $limit;

        return Connection::fetchAll($sql, $params);
    }
}
