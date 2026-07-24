# Validation & Business Rules — Client Checklist

Every rule below was inferred from the workbook, not stated in a spec — none of it came from Fairhaven telling us "a charge rate can't be below cost rate" or "job numbers look like J-3001." We read the data and built rules to match what it showed us. That means some of these could be wrong: a coincidence in this particular workbook that isn't actually a rule the business follows, or a rule the business follows but didn't happen to break in this data.

Please go through each item and mark it **✅ Correct** or **✏️ Needs changing** (with a note on what it should be instead). Items marked **⚠️** are ones we're specifically unsure about and would like an explicit answer on, even if the answer is "yes, that's right."

## Client

| # | Rule | ✅ / ✏️ | Notes |
|---|---|---|---|
| 1 | Name is required. | | |
| 2 | Phone number is optional. When entered, it's automatically reformatted to standard Australian style — `04XX XXX XXX` for mobiles, `0X XXXX XXXX` for landlines — no matter how it's typed in (with or without spaces, dashes, a `+61` prefix). Numbers that don't fit a recognisable 10-digit Australian shape are left exactly as typed. | | |
| 3 | Email is optional, but if entered must be a valid email address and must be **unique** — no two clients can share one. | | |
| 4 | ⚠️ Clients are matched/de-duplicated by **email address**, not by name — so "Harrow Property Care" and "Harrow Property Care Pty Ltd" are treated as the *same* client if their email matches. If two clients have no email on file, they're matched by name instead. Is email always a reliable match key for you, or do you have clients who share a mailbox (e.g. a shared office inbox) but are genuinely different accounts? | | |

## Technician

| # | Rule | ✅ / ✏️ | Notes |
|---|---|---|---|
| 5 | Name is required. | | |
| 6 | ⚠️ Role is optional, but limited to exactly two options: **Technician** and **Senior Technician** — the only two values found anywhere in the staff list. If there are other role titles used internally (apprentice, subcontractor, etc.), this list needs to grow. | | |
| 7 | Licence Number is optional, free text. | | |
| 8 | Active is a yes/no flag; new technicians default to **Active**. | | |
| 9 | Cost Rate (what a technician costs the business per hour) is optional, must be **$0 or more**. | | |
| 10 | Charge Rate (what the client is billed per hour, ex GST) is optional, must be **$0 or more**. | | |
| 11 | ⚠️ **Charge Rate must be greater than or equal to Cost Rate** — the form blocks saving a technician who'd be billed out below what they cost. Is this always true, or are there legitimate cases (e.g. a loss-leader job, an apprentice being trained on paid time) where charge could be set below cost? | | |
| 12 | ⚠️ A technician who logs hours in the Timesheets but isn't on the Staff List is automatically created as a new technician record with **no rate information** at all (not zero — blank/unknown), rather than blocking the import. Is that the right default, or should an unlisted technician block the import for manual review instead? | | |

## Job

| # | Rule | ✅ / ✏️ | Notes |
|---|---|---|---|
| 13 | ⚠️ Job Number is required, must be **unique**, and must match the pattern `J-` followed by 3 or more digits (e.g. `J-3001`) — inferred because every job number in the workbook follows this exact shape. Is this the permanent numbering scheme, or could future jobs use a different prefix/format? | | |
| 14 | ⚠️ Status is required and limited to four fixed options — **Open, Scheduled, In Progress, Complete** — normalised from free-text wording in the sheet (e.g. "done" and "completed" both became "Complete"). Defaults to **Open** for new jobs. Is this the complete list of statuses the business actually uses? | | |
| 15 | Client is required for new jobs created through the panel. (The one exception: "legacy" jobs auto-created from orphaned timesheet rows during import have no client — see #19 below.) | | |
| 16 | Lead Technician is optional. The dropdown only offers technicians currently marked **Active** — an inactive technician can't be assigned as lead on a new/edited job. | | |
| 17 | ⚠️ Job Type is optional, limited to four fixed options — **Breakdown, Scheduled Maintenance, Install, Repair** — also inferred from the data. Is this the complete list? | | |
| 18 | Amount (ex GST) is optional, must be **$0 or more**. | | |
| 19 | Date Completed, if set, **cannot be earlier than** Date Booked. | | |
| 20 | ⚠️ A timesheet row referencing a job number that doesn't exist in the Jobs sheet automatically creates a placeholder "legacy" job stub (no client, status Open, a note explaining it's a carry-over) rather than being rejected. Should this instead block the import so someone can investigate the missing job first? | | |

## Time Entry (Timesheets)

| # | Rule | ✅ / ✏️ | Notes |
|---|---|---|---|
| 21 | Technician is required. | | |
| 22 | Job is required. | | |
| 23 | Date is required. | | |
| 24 | Hours is required and must be a **whole or half-hour** value (7 or 7.5 — not 7.25), inferred because every hours value in the workbook is a whole or half number. | | |
| 25 | ⚠️ **Negative hours are allowed**, specifically so a mistaken entry can be corrected by logging an offsetting negative amount rather than editing or deleting the original row (e.g. one entry of `+4` and a later one of `-4` net to zero, and both stay visible). Is that how you'd want corrections handled day to day, or would you rather technicians/office staff edit the original entry directly? | | |
| 26 | Notes is optional, free text. | | |
| 27 | ⚠️ Abbreviated or differently-spelled technician names in the Timesheets sheet (e.g. "D. Nguyen") are automatically matched to the full Staff List name ("Danh Nguyen") by first-initial + surname. Is first-initial + surname a safe matching rule for your staff, or could two technicians share an initial and surname combination? | | |

---

Items 4, 6, 11, 12, 13, 14, 17, 20, 25, and 27 are also written up in more detail in [`FINDINGS.md`](FINDINGS.md), with the specific rows from the workbook that led to each decision.
