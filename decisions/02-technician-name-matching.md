# 2. The same technician is written two different ways

**What we found**
Timesheets almost always refers to technicians by a short form — "D. Nguyen", "T. Wallace", "S. O'Brien", "P. Raman" — but one row spells it out in full: "Danh Nguyen." That's the same person as "D. Nguyen" everywhere else, and matches the full name on the staff list.

**What we did**
The import matches timesheet names to the staff list, not by an exact text match, but by initial + surname. So "D. Nguyen" and "Danh Nguyen" both resolve to the one technician record. Matching on exact text would have created a duplicate "Danh Nguyen" technician with only one hour entry against him, splitting one person's hours across two records.

**Ask the client?**
No — this is a straightforward data-cleaning fix, not a judgement call.
