<?php

declare(strict_types=1);

namespace Liszted\Tests\Entity;

use Liszted\Entity\City;
use Liszted\Entity\Contributer;
use Liszted\Entity\Contribution;
use Liszted\Entity\EpsImport;
use Liszted\Entity\Performance;
use Liszted\Entity\Programme;
use Liszted\Entity\ProgrammeLine;
use Liszted\Entity\Role;
use Liszted\Entity\Series;
use Liszted\Entity\User;
use Liszted\Entity\Venue;
use Liszted\Entity\Work;
use Liszted\Tests\DatabaseTestCase;

class EntityTest extends DatabaseTestCase
{
    public function testCityFind(): void
    {
        $city = City::find(1);
        $this->assertNotNull($city);
        $this->assertSame('London', $city->name);
        $this->assertSame('GB', $city->country);
        $this->assertSame('', $city->state);
    }

    public function testCityFindReturnsNullForMissing(): void
    {
        $city = City::find(99999);
        $this->assertNull($city);
    }

    public function testCityAll(): void
    {
        $cities = City::all();
        $this->assertCount(3, $cities);
        $this->assertSame('London', $cities[0]->name);
        $this->assertSame('New York', $cities[1]->name);
        $this->assertSame('Los Angeles', $cities[2]->name);
    }

    public function testContributerFind(): void
    {
        $c = Contributer::find(1);
        $this->assertNotNull($c);
        $this->assertSame('Ludwig van', $c->first_name);
        $this->assertSame('Beethoven', $c->last_name);
        $this->assertSame(1, $c->role_primary);
    }

    public function testContributerFullName(): void
    {
        $c = Contributer::find(2);
        $this->assertNotNull($c);
        $this->assertSame('Martha Argerich', $c->fullName());
    }

    public function testContributionFind(): void
    {
        $c = Contribution::find(1);
        $this->assertNotNull($c);
        $this->assertSame(1, $c->contributer);
        $this->assertSame(1, $c->role);
        $this->assertSame(1, $c->programme);
        $this->assertSame(1, $c->programme_line);
    }

    public function testRoleFind(): void
    {
        $role = Role::find(1);
        $this->assertNotNull($role);
        $this->assertSame('Composer', $role->name);
        $this->assertSame('composed by', $role->participle);
    }

    public function testRoleAll(): void
    {
        $roles = Role::all();
        $this->assertCount(3, $roles);
    }

    public function testVenueFind(): void
    {
        $venue = Venue::find(1);
        $this->assertNotNull($venue);
        $this->assertSame('Royal Festival Hall', $venue->name);
        $this->assertSame(1, $venue->city);
    }

    public function testUserFind(): void
    {
        $user = User::find(1);
        $this->assertNotNull($user);
        $this->assertSame('Test User', $user->name);
        $this->assertSame('testuser', $user->username);
    }

    public function testUserFindByUsername(): void
    {
        $user = User::findByUsername('testuser');
        $this->assertNotNull($user);
        $this->assertSame(1, $user->id);
    }

    public function testUserFindByUsernameMissing(): void
    {
        $user = User::findByUsername('nonexistent');
        $this->assertNull($user);
    }

    public function testSeriesFind(): void
    {
        $series = Series::find(1);
        $this->assertNotNull($series);
        $this->assertSame('Test Season 2024', $series->name);
        $this->assertSame(1, $series->user);
    }

    public function testProgrammeFind(): void
    {
        $prog = Programme::find(1);
        $this->assertNotNull($prog);
        $this->assertSame('Evening of Beethoven', $prog->title);
        $this->assertSame(1, $prog->series);
        $this->assertSame(0, $prog->hidden);
        $this->assertSame(1, $prog->show_works);
    }

    public function testProgrammeLineFind(): void
    {
        $pl = ProgrammeLine::find(1);
        $this->assertNotNull($pl);
        $this->assertSame('Piano Concerto No. 5', $pl->text);
        $this->assertSame(1, $pl->programme);
    }

    public function testProgrammeLineFindByProgramme(): void
    {
        $lines = ProgrammeLine::findByProgramme(1);
        $this->assertCount(2, $lines);
        $this->assertSame('Piano Concerto No. 5', $lines[0]->text);
        $this->assertSame('Symphony No. 7', $lines[1]->text);
    }

    public function testPerformanceFind(): void
    {
        $perf = Performance::find(1);
        $this->assertNotNull($perf);
        $this->assertSame(1, $perf->programme);
        $this->assertSame(1, $perf->venue);
        $this->assertSame('2025-03-15 19:30:00', $perf->start);
    }

    public function testPerformanceFindByProgramme(): void
    {
        $perfs = Performance::findByProgramme(1);
        $this->assertCount(2, $perfs);
    }

    public function testWorkFind(): void
    {
        $work = Work::find(1);
        $this->assertNotNull($work);
        $this->assertSame('imslp-beethoven-pc5', $work->external_id);
    }

    public function testFromRowPreservesNulls(): void
    {
        $row = [
            'id' => '5',
            'first_name' => null,
            'last_name' => null,
            'role_primary' => null,
        ];
        $c = Contributer::fromRow($row);
        $this->assertNull($c->first_name);
        $this->assertNull($c->last_name);
        $this->assertNull($c->role_primary);
        $this->assertSame(5, $c->id);
    }
}
