<?php

declare(strict_types=1);

namespace Liszted\Tests;

use Liszted\Database\Connection;
use PHPUnit\Framework\TestCase;
use PDO;
use Vimeo\MysqlEngine\FakePdo;

abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        // Use php-mysql-engine's FakePdo — no real MySQL server needed
        // Note: trailing semicolon after dbname is required by FakePdo's regex
        $this->pdo = FakePdo::getFakePdo(
            'mysql:host=127.0.0.1;dbname=liszted;',
            'root',
            '',
            [PDO::ATTR_EMULATE_PREPARES => false]
        );
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        Connection::setPdo($this->pdo);
        $this->createSchema();
        $this->seedData();
    }

    protected function tearDown(): void
    {
        Connection::reset();
        parent::tearDown();
    }

    private function createSchema(): void
    {
        // Drop all tables first to ensure clean state between tests
        foreach (['contribution', 'programme_line', 'performance', 'programme', 'series', 'venue', 'city', 'contributer', 'role', 'user', 'work', 'eps_import'] as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        }

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `city` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL DEFAULT \'\',
            `state` varchar(31) DEFAULT \'\',
            `country` varchar(2) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`id`),
            UNIQUE KEY `place` (`name`,`state`,`country`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `role` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `participle` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `contributer` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `first_name` varchar(255) DEFAULT NULL,
            `last_name` varchar(255) DEFAULT NULL,
            `role_primary` int(10) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `user` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL DEFAULT \'\',
            `hide` tinyint(1) NOT NULL,
            `thumbnail` varchar(255) DEFAULT NULL,
            `hash` varchar(255) DEFAULT NULL,
            `username` varchar(255) NOT NULL DEFAULT \'\',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `series` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `user` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `programme` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `series` int(11) unsigned NOT NULL,
            `url` varchar(255) DEFAULT NULL,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_accuracy` int(11) DEFAULT NULL,
            `hidden` tinyint(1) NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `show_works` tinyint(1) NOT NULL,
            `description` longtext,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `venue` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT \'\',
            `street` varchar(255) DEFAULT NULL,
            `city` int(10) unsigned DEFAULT NULL,
            `zip` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `performance` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `programme` int(11) unsigned DEFAULT NULL,
            `start` datetime DEFAULT NULL,
            `end` datetime DEFAULT NULL,
            `venue` int(10) unsigned DEFAULT NULL,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `type` varchar(255) DEFAULT NULL,
            `url` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `programme_line` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `programme` int(10) unsigned DEFAULT NULL,
            `text` varchar(255) DEFAULT NULL,
            `work` int(11) unsigned DEFAULT NULL,
            `url` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `contribution` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `contributer` int(11) unsigned DEFAULT NULL,
            `role` int(11) unsigned DEFAULT NULL,
            `series` int(11) unsigned DEFAULT NULL,
            `programme` int(11) unsigned DEFAULT NULL,
            `programme_line` int(10) unsigned DEFAULT NULL,
            `performance` int(11) unsigned DEFAULT NULL,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `work` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `external_id` varchar(127) DEFAULT NULL,
            `url` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->pdo->exec('CREATE TABLE IF NOT EXISTS `eps_import` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `title` varchar(255) DEFAULT \'\',
            `location` varchar(255) NOT NULL DEFAULT \'\',
            `city` varchar(255) NOT NULL DEFAULT \'\',
            `country` varchar(2) DEFAULT NULL,
            `date` date NOT NULL,
            `details` longtext NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    protected function seedData(): void
    {
        // Roles
        $this->pdo->exec("INSERT INTO `role` (id, name, participle) VALUES (1, 'Composer', 'composed by')");
        $this->pdo->exec("INSERT INTO `role` (id, name, participle) VALUES (2, 'Pianist', 'performed by')");
        $this->pdo->exec("INSERT INTO `role` (id, name, participle) VALUES (3, 'Conductor', 'conducted by')");

        // Cities
        $this->pdo->exec("INSERT INTO `city` (id, name, state, country) VALUES (1, 'London', '', 'GB')");
        $this->pdo->exec("INSERT INTO `city` (id, name, state, country) VALUES (2, 'New York', 'NY', 'US')");
        $this->pdo->exec("INSERT INTO `city` (id, name, state, country) VALUES (3, 'Los Angeles', 'CA', 'US')");

        // Venues
        $this->pdo->exec("INSERT INTO `venue` (id, name, city) VALUES (1, 'Royal Festival Hall', 1)");
        $this->pdo->exec("INSERT INTO `venue` (id, name, city) VALUES (2, 'Carnegie Hall', 2)");
        $this->pdo->exec("INSERT INTO `venue` (id, name, city) VALUES (3, 'Walt Disney Concert Hall', 3)");

        // Users
        $this->pdo->exec("INSERT INTO `user` (id, name, hide, username) VALUES (1, 'Test User', 0, 'testuser')");

        // Contributors
        $this->pdo->exec("INSERT INTO `contributer` (id, first_name, last_name, role_primary) VALUES (1, 'Ludwig van', 'Beethoven', 1)");
        $this->pdo->exec("INSERT INTO `contributer` (id, first_name, last_name, role_primary) VALUES (2, 'Martha', 'Argerich', 2)");
        $this->pdo->exec("INSERT INTO `contributer` (id, first_name, last_name, role_primary) VALUES (3, 'Simon', 'Rattle', 3)");

        // Series
        $this->pdo->exec("INSERT INTO `series` (id, name, user) VALUES (1, 'Test Season 2024', 1)");

        // Programmes
        $this->pdo->exec("INSERT INTO `programme` (id, series, hidden, show_works, title) VALUES (1, 1, 0, 1, 'Evening of Beethoven')");
        $this->pdo->exec("INSERT INTO `programme` (id, series, hidden, show_works, title) VALUES (2, 1, 0, 1, 'Piano Recital')");

        // Programme lines
        $this->pdo->exec("INSERT INTO `programme_line` (id, programme, text) VALUES (1, 1, 'Piano Concerto No. 5')");
        $this->pdo->exec("INSERT INTO `programme_line` (id, programme, text) VALUES (2, 1, 'Symphony No. 7')");
        $this->pdo->exec("INSERT INTO `programme_line` (id, programme, text) VALUES (3, 2, 'Piano Sonata No. 14')");

        // Performances
        $this->pdo->exec("INSERT INTO `performance` (id, programme, start, venue) VALUES (1, 1, '2025-03-15 19:30:00', 1)");
        $this->pdo->exec("INSERT INTO `performance` (id, programme, start, venue) VALUES (2, 1, '2025-03-16 19:30:00', 2)");
        $this->pdo->exec("INSERT INTO `performance` (id, programme, start, venue) VALUES (3, 2, '2025-04-01 20:00:00', 3)");

        // Contributions
        $this->pdo->exec("INSERT INTO `contribution` (id, contributer, role, programme, programme_line) VALUES (1, 1, 1, 1, 1)");
        $this->pdo->exec("INSERT INTO `contribution` (id, contributer, role, programme) VALUES (2, 2, 2, 1)");
        $this->pdo->exec("INSERT INTO `contribution` (id, contributer, role, programme) VALUES (3, 3, 3, 1)");
        $this->pdo->exec("INSERT INTO `contribution` (id, contributer, role, programme, programme_line) VALUES (4, 1, 1, 2, 3)");
        $this->pdo->exec("INSERT INTO `contribution` (id, contributer, role, programme) VALUES (5, 2, 2, 2)");

        // Works
        $this->pdo->exec("INSERT INTO `work` (id, external_id) VALUES (1, 'imslp-beethoven-pc5')");
    }
}
