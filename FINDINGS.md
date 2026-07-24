# Findings & Decisions

Everything in the workbook that was inconsistent, contradictory, or simply wrong, what we decided to do about it, and whether it's worth asking the client. Each item has its own short write-up in [`decisions/`](decisions/) so they can be shared individually if needed.

Import produces: **6 technicians** (5 from the staff list + 1 discovered), **8 clients**, **31 jobs** (30 from the Jobs sheet + 1 legacy stub), **50 time entries**.

| # | Decision | Ask the client? |
|---|---|---|
| 1 | [A job can have more than one technician](decisions/01-job-technician-relationship.md) | No |
| 2 | [The same technician is written two different ways](decisions/02-technician-name-matching.md) | No |
| 3 | [A technician logs hours but isn't on the staff list](decisions/03-unlisted-technician.md) | **Yes** |
| 4 | [Two entries cancel each other out, with no correct number left behind](decisions/04-cancelling-time-entries.md) | **Yes** |
| 5 | [Job status is typed freely, with no fixed list of options](decisions/05-job-status-wording.md) | No |
| 6 | [Dates are written in three different formats](decisions/06-date-formats.md) | No |
| 7 | [One client appears under two names](decisions/07-duplicate-client-name.md) | Worth a quick check |
| 8 | [An hours entry references a job that doesn't exist](decisions/08-orphan-timesheet-job.md) | **Yes** |
| 9 | [One client has jobs at two different addresses](decisions/09-multi-site-client.md) | Worth a quick check |
| 10 | [A staff member with no recorded work](decisions/10-inactive-technician.md) | No |
| 11 | [Hours are always logged in whole or half-hour amounts](decisions/11-half-hour-rule.md) | No |
| 12 | [Assumptions we made without asking](decisions/12-assumptions.md) | No |

See [`DATA_MODEL.md`](DATA_MODEL.md) for how these decisions shape the database structure.
