# Liszted

Concert and performance listing application. Manages programmes, performances, venues, contributors, and roles for classical music artists.

## Commands

- `composer install` — install dependencies
- `vendor/bin/phpunit` — run tests (no MySQL server needed; uses php-mysql-engine FakePdo)
- `vendor/bin/psalm` — run static analysis

## Architecture

No framework. PHP 8.3+, Composer PSR-4 autoloading under the `Liszted\` namespace.

### Layers

- **`src/Entity/`** — Data classes mapping 1:1 to database tables. Each has `fromRow()`, `find()`, and `all()`. These are for loading rows; they do not contain business logic.
- **`src/Controller/`** — Business logic. Controllers that query the database, build models, and handle saves. `ProgrammeController` is the most complex; it assembles full programme views with works, venues, and contributors.
- **`src/Model/`** — View models / DTOs (`ProgrammeModel`, `VenueModel`, etc.) used to pass structured data to templates. These are distinct from entities.
- **`src/Database/Connection.php`** — Static PDO wrapper. All queries use prepared statements with `?` placeholders. Supports `fetch()`, `fetchAll()`, `insert()`, `update()`, `upsert()`, `execute()`. The PDO instance can be swapped via `setPdo()` for testing.
- **`public/`** — Web root. Entry points, static assets (CSS/JS/images).
- **`data/geo.php`** — Country and US state lookup arrays, loaded by `Constants::init()`.

### Database

MySQL. 12 tables: `city`, `contributer`, `contribution`, `eps_import`, `performance`, `programme`, `programme_line`, `role`, `series`, `user`, `venue`, `work`. Schema is in `liszted_2026-03-19_22-14-13.sql`. The tables are not expected to change.

Database credentials come from environment variables (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) or can be set via `Connection::configure()`.

### Testing

PHPUnit 10 with `vimeo/php-mysql-engine` (FakePdo). Tests extend `DatabaseTestCase`, which creates all tables in-memory, seeds test data, and injects the fake PDO into `Connection`. No real MySQL server is required.

## Conventions

- `declare(strict_types=1)` in every PHP file.
- All SQL uses prepared statements — never interpolate user input into queries.
- HTML output uses `htmlspecialchars()` for XSS prevention.
- The misspelling "contributer" (instead of "contributor") is intentional — it matches the database column and table names.
- `UserSession` uses PHP sessions, not cookies, for authentication state.
- `Constants::init()` must be called before accessing `$countries` or `$us_states`.
