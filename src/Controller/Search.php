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
        SELECT *,
            (SELECT start FROM performance WHERE programme = programme.id ORDER BY start ASC LIMIT 1) AS start
        FROM programme
        WHERE hidden = 0
          AND id IN (
            SELECT programme FROM performance WHERE id IN (
                SELECT performance FROM contribution WHERE contributer = ?
            )
            OR programme IN (
                SELECT programme FROM contribution WHERE contributer = ?
            )
            OR programme IN (
                SELECT id FROM programme WHERE series IN (
                    SELECT series FROM contribution WHERE contributer = ?
                )
            )
            OR programme IN (
                SELECT (SELECT programme FROM programme_line WHERE programme_line.id = contribution.programme_line) AS programme
                FROM contribution WHERE contributer = ?
            )
          )";

        $params = [$contributerId, $contributerId, $contributerId, $contributerId];

        if ($futureOnly) {
            $sql .= " AND (SELECT start FROM performance WHERE programme = programme.id ORDER BY start DESC LIMIT 1) > ?";
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

        $sql = "SELECT *,
            (SELECT start FROM performance WHERE programme = programme.id ORDER BY start ASC LIMIT 1) AS start
        FROM programme
        WHERE hidden = 0
          AND (SELECT user FROM series WHERE series.id = programme.series) = ?";

        $params = [$userId];

        if ($futureOnly) {
            $sql .= " AND (SELECT start FROM performance WHERE programme = programme.id ORDER BY start DESC LIMIT 1) > ?";
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
        SELECT *,
            (SELECT start FROM performance WHERE programme = programme.id ORDER BY start ASC LIMIT 1) AS start
        FROM programme
        WHERE hidden = 0
          AND id IN (
            SELECT programme FROM programme_line WHERE text LIKE ?
            AND programme_line.id IN (
                SELECT programme_line FROM contribution WHERE contributer = ? AND programme_line IS NOT NULL
            )
          )";

        $params = [$work, $contributerId];

        if ($futureOnly) {
            $sql .= " AND (SELECT start FROM performance WHERE programme = programme.id ORDER BY start DESC LIMIT 1) > ?";
            $params[] = $yesterday;
        }

        $sql .= " ORDER BY start LIMIT ?";
        $params[] = $limit;

        return Connection::fetchAll($sql, $params);
    }
}
