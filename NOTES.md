# Notes

## What I built

A Laravel + Filament 5 panel over four real entities (Client, Technician, Job, TimeEntry) derived from the three source sheets — not four tables mapped 1:1 to three sheets, since the real Job↔Technician relationship is many-to-many and Client isn't its own sheet at all (see `DATA_MODEL.md`). The import is a single idempotent Artisan command reading the actual `.xlsx` workbook via `maatwebsite/excel` (pinned to a specific commit on its unreleased `4.x` branch — its only stable release line caps out below this project's PHP/Laravel versions; see the README for the detail). All ten data issues in `FINDINGS.md` are handled at import time, not papered over in the UI.

The panel covers everything the brief asked for: a job page showing client, lead technician, every technician's hours, and status; a client's job history; a technician's total hours and work log; an "Outstanding" jobs tab; create/edit forms with validation drawn from what the data actually showed (fixed status/type vocabularies, `J-####` format, completion-after-booking, charge-rate-at-least-cost-rate, and decimal ranges matching each column's actual precision so the database can't reject a save the form already accepted); and — since there was time — a labour-value section on each job comparing the quoted amount against hours × cost rate and hours × charge rate, so the business can see margin per job for the first time.

A second pass went specifically after office usability: labels reworded for a bookkeeper rather than a developer ("Job Number" and "Licence Number" instead of "No"), full-word date formatting (`25 December 2026`), Australian phone numbers normalised and formatted consistently regardless of how they're typed in, click-to-call/copy on phone fields, searchable columns on everything an office user would actually search by, and create/edit forms that return to the list page on save instead of Filament's default of parking you on the record.

## What I left out

- **No custom design work** — Filament's default panel styling, per the brief's scope.
- **No Sites entity** — Halden Logistics operates from two addresses under one client; address stays on the Job for now rather than building out a proper site model, since one client isn't enough evidence to justify the extra table yet.
- **No policies/roles** — every authenticated user can do everything, matching "authentication beyond Laravel's default" being out of scope.
- **The two genuine open questions in `FINDINGS.md`** (J. Fitzsimmons' employment status and rate; the actual hours behind the Raman correction entry) are surfaced honestly in the UI as missing data, not guessed at.
- **Test coverage is targeted, not exhaustive** — one feature test proving the import handles the tricky cases correctly against the real committed workbook (merged technician names, the multi-technician job, the legacy stub job, the self-cancelling correction entry, idempotency on re-run), one unit test for the importer's pure parsing/matching helpers. No panel-level smoke tests — Filament's own CRUD scaffolding is well-tested upstream, so that time went into the import logic, where the real complexity actually lives. Per the brief: "less work finished properly" over broad coverage.
- **No in-panel re-import** — loading a corrected workbook is a CLI command (`sail artisan fairhaven:import`), not a file upload inside the panel. Fine for a developer, not yet for the office.
- **No audit trail** — no record of who changed a rate or a job amount. Worth having before more than one office person uses this day to day, given it's now the source of truth for figures that feed into billing.

## What I'd do next

- Send the two open questions from `FINDINGS.md` to the client before building anything further on top of them (a subcontractor rate policy; what actually happened on J-3022).
- If Halden Logistics turns out to be typical rather than an outlier, promote site address to its own entity.
- A "jobs needing a completion date" or "unbilled completed jobs" view — the outstanding-jobs tab covers status, but a completed job with no `Amount` set is a different kind of "not actually finished" that the office would want flagged separately.
- Bulk timesheet entry (a week-at-a-glance grid per technician) instead of one row at a time, since that's closer to how technicians actually described entering their hours.
- An in-panel workbook upload, so re-importing a corrected sheet doesn't need shell access.
- A basic audit trail on `Job` and `TimeEntry`, since both now carry figures that feed into billing.

## Time spent

- **Reading and analysing the trial task (30–45 min)** — re-read the brief against the spreadsheet more than once; the "Assigned To isn't the whole story" relationship only became obvious by cross-referencing Jobs against Timesheets row by row, not from the column headings.
- **Backend development — models, migrations, import command (1hr+)** — schema for the four entities, the idempotent `fairhaven:import` command reading the workbook via `maatwebsite/excel`, working through the data issues now written up in `FINDINGS.md`, plus a unit test for the importer's parsing/matching helpers alongside the feature test against the real workbook.
- **Frontend development — Filament panel (1hr+)** — built resource by resource: Clients, Technicians, Jobs, Time Entries, plus the relation managers linking them (a client's jobs, a technician's time entries) and the Outstanding-jobs tab and labour-margin view on top.
- **Validation and office-usability pass (30min+)** — form validation matched to the DB column constraints, bookkeeper-facing labels, date/phone formatting, searchable columns, redirect-to-list on save.
- **Write-up — `DATA_MODEL.md`, `FINDINGS.md`, `decisions/`, this file (30min+)**

**Total: _fill in_**
