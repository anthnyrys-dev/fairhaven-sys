# 11. Hours are always logged in whole or half-hour amounts

**What we found**
Every single one of the 50 real time entries — including the negative correction row — is a whole number or ends in .5 (7, 7.5, 8, 9.5, 2.5, −6.5, and so on). Nothing like 7.25 or 7.75 appears anywhere.

**What we did**
We treated this as a genuine business rule rather than a coincidence — trade timesheets are commonly logged in half-hour blocks. The hours field now rejects anything that isn't a whole number or a half hour (e.g. "7.25" gets a plain "must be a whole or half hour" message), while whole numbers, halves, and negative corrections are all still accepted.

**Ask the client?**
No — the pattern was clean across every row, but this is easy to loosen later if it turns out technicians sometimes need finer detail.
