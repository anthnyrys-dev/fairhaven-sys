# Fairhaven Mechanical Services

A Laravel + Filament panel that replaces Fairhaven's three uncoordinated spreadsheets (job book, technician timesheets, staff list) with one system, built from the actual structure underneath the data rather than a literal sheet-to-table copy.

Start with [`DATA_MODEL.md`](DATA_MODEL.md) for the entities and the one relationship that isn't what the column headings imply, and [`FINDINGS.md`](FINDINGS.md) for every data-quality issue found, what was done about it, and what's worth asking the client.

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or a compatible engine) — the app, PostgreSQL, and Redis all run as containers via [Laravel Sail](https://laravel.com/docs/sail); no local PHP install is required.
- Node.js (for the Tailwind/Vite frontend assets — not required for the Filament panel itself, which ships its own compiled assets)

## Setup

```bash
cp .env.example .env

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate

# Migrates the PostgreSQL database, seeds an admin user, and imports the
# workbook — a clean database, in one step.
./vendor/bin/sail artisan migrate:fresh --seed

./vendor/bin/sail npm install
./vendor/bin/sail npm run build   # or `sail npm run dev` while working on it
```

(Already have PHP 8.5 and Composer installed locally? `composer install` and `npm install` work the same without the container detour — Sail only needs Docker for `up`.)

Open `http://localhost:8083/admin` or `http://fairhaven-sys.test/admin` and sign in with:

- **Email:** `office@fairhaven.test`
- **Password:** `password`

(Panel access defaults to any authenticated user when `APP_ENV=local`, which is what `.env.example` sets — this is Laravel/Filament's own default, nothing custom was built for auth, per the brief's scope.)

The database and app ports are fixed in [`compose.yaml`](compose.yaml): the app on host port `8083`, PostgreSQL on `5435` (`DB_DATABASE=laravel`, `DB_USERNAME=sail`, `DB_PASSWORD=password` — see `.env.example`), and Redis on `6379`.

## Re-importing the workbook

The import is idempotent — it always clears and reloads `clients`, `technicians`, `work_orders` (the Jobs table — see below), and `time_entries`, so it's safe to re-run:

```bash
./vendor/bin/sail artisan fairhaven:import
```

The real workbook is committed at `database/data/Trial-Task-Data-Fairhaven.xlsx`. To import a different file:

```bash
./vendor/bin/sail artisan fairhaven:import /path/to/another-workbook.xlsx
```

The `fairhaven:import` command (`app/Console/Commands/ImportFairhavenWorkbook.php`) is a thin CLI wrapper — the actual import logic lives in `App\Services\FairhavenWorkbookImporter`, reading via Laravel Excel (`app/Imports/FairhavenWorkbookImport.php` + `SheetToArrayImport.php`). Keeping the logic in a service rather than the command means it can be exercised directly in tests without going through the console kernel. The command is also wired into `database/seeders/DatabaseSeeder.php`, so `migrate:fresh --seed` does the whole setup in one command.

**Note on the `maatwebsite/excel` dependency**: its only stable release line (3.1.x) caps out at Laravel ^12 and PHP <8.5, so it can't be installed on this project's Laravel 13 / PHP 8.5 stack. The project pins the package to a specific commit on its unreleased `4.x` branch (`composer.json`'s `4.x-dev#a9a16f1...`) rather than floating on `dev-4.x`, so the dependency can't silently change underneath the app before that branch is tagged stable.

## Why the Job table is called `work_orders`

Laravel's own queue system already owns a table called `jobs` (see `database/migrations/0001_01_01_000002_create_jobs_table.php`, present in every fresh Laravel install). The business entity is still `App\Models\Job` in code and "Job" everywhere in the UI — only the underlying table name had to move to avoid the collision.

## Tests

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail composer types:check   # Larastan
./vendor/bin/sail pint                   # code style
```

`tests/Feature/FairhavenImportTest.php` runs the import (via the `fairhaven:import` command) against the real committed workbook and asserts the tricky cases resolve correctly (merged technician names, the discovered technician, the multi-technician job, the legacy stub job, the self-cancelling correction entry). `tests/Unit/FairhavenWorkbookImporterTest.php` covers `App\Services\FairhavenWorkbookImporter` directly — its pure parsing/matching helpers (date formats, decimal parsing, status/type/role matching) in isolation, plus the public `import()` method's return counts and idempotency against the real workbook.

## What the panel does

- **Jobs** — list defaults to an "Outstanding" tab (anything not `Complete`), with a badge count. Opening a job shows the client, the lead technician, every technician who logged hours against it with their hours, and — if rates are on file — how the quoted amount compares to labour cost.
- **Clients** — job history per client.
- **Technicians** — every job worked and total hours logged, plus charge/cost rate on file.
- **Timesheets** — direct hour entry, independent of opening a job (mirrors how technicians actually log hours week to week).

Validation reflects the rules inferred from the data: Job No must match `J-####`, job status and type are fixed lists (not free text), a completion date can't be before the booking date, and a technician's charge rate can't be set below their cost rate.
