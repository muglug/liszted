<?php

declare(strict_types=1);

namespace Liszted\Tests\Database;

use Liszted\Controller\Search;
use Liszted\Controller\Updater;
use Liszted\Database\Connection;
use Liszted\Tests\DatabaseTestCase;

class SearchTest extends DatabaseTestCase
{
    public function testContributerSearchByFullName(): void
    {
        $id = Search::contributer('Ludwig van', 'Beethoven');
        $this->assertSame(1, $id);
    }

    public function testContributerSearchNotFound(): void
    {
        $id = Search::contributer('Nonexistent', 'Person');
        $this->assertSame(-1, $id);
    }

    public function testRolesReturnsParticiples(): void
    {
        $roles = Search::roles();
        $this->assertCount(3, $roles);

        $participles = array_map(fn(array $r): string => $r[1], $roles);
        $this->assertContains('composed by', $participles);
        $this->assertContains('performed by', $participles);
        $this->assertContains('conducted by', $participles);
    }

    public function testContributersReturnsNamesWithRoles(): void
    {
        $names = Search::contributers();
        $this->assertGreaterThanOrEqual(3, count($names));
        $this->assertContains('Ludwig van Beethoven', $names);
        $this->assertContains('Martha Argerich', $names);
    }

    public function testProgrammesByContributer(): void
    {
        $programmes = Search::programmesByContributer(2); // Martha Argerich
        $this->assertGreaterThanOrEqual(2, count($programmes));

        $titles = array_column($programmes, 'title');
        $this->assertContains('Evening of Beethoven', $titles);
        $this->assertContains('Piano Recital', $titles);
    }

    public function testProgrammesByUser(): void
    {
        $programmes = Search::programmesByUser(1);
        $this->assertCount(2, $programmes);
    }

    public function testUpdaterCity(): void
    {
        // Should find existing city
        $id1 = Updater::city('London', '', 'GB');
        $this->assertSame(1, (int) $id1);

        // Should create new city
        $id2 = Updater::city('Paris', '', 'FR');
        $this->assertGreaterThan(3, (int) $id2);

        // Should find the newly created city
        $id3 = Updater::city('Paris', '', 'FR');
        $this->assertSame((int) $id2, (int) $id3);
    }

    public function testUpdaterContributer(): void
    {
        // Should find existing
        $id1 = Updater::contributer('Ludwig van', 'Beethoven');
        $this->assertSame(1, (int) $id1);

        // Should create new
        $id2 = Updater::contributer('Clara', 'Schumann');
        $this->assertGreaterThan(3, (int) $id2);
    }

    public function testUpdaterRole(): void
    {
        // Should find existing
        $id1 = Updater::role('Composer');
        $this->assertSame(1, (int) $id1);

        // Should create new
        $id2 = Updater::role('Soprano');
        $this->assertGreaterThan(3, (int) $id2);
    }

    public function testUpdaterVenue(): void
    {
        // Should find existing
        $id1 = Updater::venue('Royal Festival Hall', 1);
        $this->assertSame(1, (int) $id1);

        // Should create new
        $id2 = Updater::venue('Wigmore Hall', 1);
        $this->assertGreaterThan(3, (int) $id2);
    }
}
