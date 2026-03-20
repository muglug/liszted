<?php

declare(strict_types=1);

namespace Liszted\Tests\Database;

use Liszted\Database\Connection;
use Liszted\Tests\DatabaseTestCase;

class ConnectionTest extends DatabaseTestCase
{
    public function testFetchReturnsRow(): void
    {
        $row = Connection::fetch('SELECT * FROM city WHERE id = ?', [1]);
        $this->assertNotNull($row);
        $this->assertSame('London', $row['name']);
    }

    public function testFetchReturnsNullForMissing(): void
    {
        $row = Connection::fetch('SELECT * FROM city WHERE id = ?', [99999]);
        $this->assertNull($row);
    }

    public function testFetchAll(): void
    {
        $rows = Connection::fetchAll('SELECT * FROM city ORDER BY id');
        $this->assertCount(3, $rows);
    }

    public function testInsertAndRetrieve(): void
    {
        $id = Connection::insert('city', [
            'name' => 'Berlin',
            'state' => '',
            'country' => 'DE',
        ]);
        $this->assertGreaterThan(0, (int) $id);

        $row = Connection::fetch('SELECT * FROM city WHERE id = ?', [(int) $id]);
        $this->assertNotNull($row);
        $this->assertSame('Berlin', $row['name']);
        $this->assertSame('DE', $row['country']);
    }

    public function testUpdate(): void
    {
        Connection::update('city', 1, ['name' => 'Greater London']);
        $row = Connection::fetch('SELECT * FROM city WHERE id = ?', [1]);
        $this->assertNotNull($row);
        $this->assertSame('Greater London', $row['name']);
    }

    public function testUpsertInsert(): void
    {
        $id = Connection::upsert(0, 'role', ['name' => 'Violinist', 'participle' => 'played by']);
        $this->assertGreaterThan(0, (int) $id);

        $row = Connection::fetch('SELECT * FROM role WHERE id = ?', [(int) $id]);
        $this->assertNotNull($row);
        $this->assertSame('Violinist', $row['name']);
    }

    public function testUpsertUpdate(): void
    {
        Connection::upsert(1, 'role', ['name' => 'Composer/Arranger']);
        $row = Connection::fetch('SELECT * FROM role WHERE id = ?', [1]);
        $this->assertNotNull($row);
        $this->assertSame('Composer/Arranger', $row['name']);
    }

    public function testFetchAllWithParams(): void
    {
        $rows = Connection::fetchAll('SELECT * FROM performance WHERE programme = ? ORDER BY start', [1]);
        $this->assertCount(2, $rows);
        $this->assertSame('2025-03-15 19:30:00', $rows[0]['start']);
        $this->assertSame('2025-03-16 19:30:00', $rows[1]['start']);
    }

    public function testJoinQuery(): void
    {
        $rows = Connection::fetchAll(
            'SELECT venue.name AS venue_name, city.name AS city_name, city.country
             FROM venue
             LEFT JOIN city ON venue.city = city.id
             WHERE venue.id = ?',
            [1]
        );
        $this->assertCount(1, $rows);
        $this->assertSame('Royal Festival Hall', $rows[0]['venue_name']);
        $this->assertSame('London', $rows[0]['city_name']);
        $this->assertSame('GB', $rows[0]['country']);
    }

    public function testSubqueryInWhere(): void
    {
        $rows = Connection::fetchAll(
            'SELECT * FROM programme WHERE id IN (
                SELECT programme FROM performance WHERE venue = ?
            )',
            [1]
        );
        $this->assertCount(1, $rows);
        $this->assertSame('Evening of Beethoven', $rows[0]['title']);
    }

    public function testContributionJoinQuery(): void
    {
        $rows = Connection::fetchAll(
            'SELECT contributer.first_name, contributer.last_name, role.name AS role_name
             FROM contribution
             LEFT JOIN contributer ON contribution.contributer = contributer.id
             LEFT JOIN role ON contribution.role = role.id
             WHERE contribution.programme = ?',
            [1]
        );
        $this->assertGreaterThanOrEqual(3, count($rows));

        $names = array_map(fn(array $r): string => $r['last_name'], $rows);
        $this->assertContains('Beethoven', $names);
        $this->assertContains('Argerich', $names);
        $this->assertContains('Rattle', $names);
    }

    public function testDeleteWithParams(): void
    {
        Connection::execute('DELETE FROM work WHERE id = ?', [1]);
        $row = Connection::fetch('SELECT * FROM work WHERE id = ?', [1]);
        $this->assertNull($row);
    }

    public function testPerformancesByProgrammeWithVenue(): void
    {
        $rows = Connection::fetchAll(
            'SELECT p.*, v.name AS venue_name, c.name AS city_name
             FROM performance p
             LEFT JOIN venue v ON p.venue = v.id
             LEFT JOIN city c ON v.city = c.id
             WHERE p.programme = ?
             ORDER BY p.start',
            [1]
        );
        $this->assertCount(2, $rows);
        $this->assertSame('Royal Festival Hall', $rows[0]['venue_name']);
        $this->assertSame('London', $rows[0]['city_name']);
        $this->assertSame('Carnegie Hall', $rows[1]['venue_name']);
        $this->assertSame('New York', $rows[1]['city_name']);
    }

    public function testProgrammeLineContributionsQuery(): void
    {
        $rows = Connection::fetchAll(
            'SELECT pl.text, c.first_name, c.last_name
             FROM programme_line pl
             LEFT JOIN contribution ct ON ct.programme_line = pl.id
             LEFT JOIN contributer c ON ct.contributer = c.id
             WHERE pl.programme = ?
             ORDER BY pl.id',
            [1]
        );
        $this->assertCount(2, $rows);
        $this->assertSame('Piano Concerto No. 5', $rows[0]['text']);
        $this->assertSame('Beethoven', $rows[0]['last_name']);
    }
}
