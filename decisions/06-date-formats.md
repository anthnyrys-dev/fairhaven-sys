# 6. Dates are written in three different formats

**What we found**
Dates show up as `06/01/2025` (day/month/year, the common case), `2025-01-22` (year-month-day, a handful of times), and once as `20 Jan 25`. All of them are Australian-style (day before month) — we cross-checked this against phone numbers and suburbs to be sure `06/01/2025` means 6 January, not June 1.

**What we did**
The import recognises each of these three formats explicitly and rejects anything else, rather than trusting a generic date parser. A generic parser typically assumes month-before-day for ambiguous dates like `06/01/2025`, which would have silently swapped the day and month for most rows in this workbook.

**Ask the client?**
No — this is a data-cleaning fix, and we're confident in the day/month/year reading.
