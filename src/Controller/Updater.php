<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Database\Connection;

class Updater
{
    public static function city(string $name, string $state, string $country): int|string
    {
        $row = Connection::fetch(
            "SELECT id FROM city WHERE name = ? AND (state = ? OR (state IS NULL AND ? = '')) AND country = ?",
            [$name, $state, $state, $country]
        );
        if ($row !== null) {
            return (int) $row['id'];
        }
        Connection::execute(
            "INSERT INTO city (name, state, country) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)",
            [$name, $state, $country]
        );
        return Connection::getPdo()->lastInsertId();
    }

    public static function contributer(string $firstName, string $lastName = "", ?int $rolePrimary = null): int|string
    {
        $contributerId = Search::contributer($firstName, $lastName);
        if ($contributerId > 0) {
            return $contributerId;
        }
        $fields = ['first_name' => $firstName, 'last_name' => $lastName];
        if ($rolePrimary !== null) {
            $fields['role_primary'] = $rolePrimary;
        }
        return Connection::insert('contributer', $fields);
    }

    public static function role(string $name): int|string
    {
        $row = Connection::fetch("SELECT id FROM role WHERE name = ?", [$name]);
        if ($row !== null) {
            return (int) $row['id'];
        }
        return Connection::insert('role', ['name' => $name]);
    }

    public static function venue(string $name, int $city = 0): int|string
    {
        $name = trim($name);
        if ($city > 0) {
            $row = Connection::fetch("SELECT id FROM venue WHERE name = ? AND city = ?", [$name, $city]);
        } else {
            $row = Connection::fetch("SELECT id FROM venue WHERE name = ?", [$name]);
        }
        if ($row !== null) {
            return (int) $row['id'];
        }
        $fields = ['name' => $name];
        if ($city > 0) {
            $fields['city'] = $city;
        }
        return Connection::insert('venue', $fields);
    }
}
