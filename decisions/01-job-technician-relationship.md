# 1. A job can have more than one technician

**What we found**
The Jobs sheet has a column called "Assigned To" with one name per job. That looks like each job belongs to exactly one technician. But the Timesheets sheet tells a different story: several jobs (J-3001, J-3006, J-3010, J-3013, J-3018, J-3022) have a *second* technician logging hours against them who never appears in "Assigned To" at all. For example, J-3001 is assigned to Danh Nguyen, but Tom Wallace also logged 4.5 hours on it "assisting, crane lift."

**What we did**
We built the system around "who logged hours on this job" as the real record of who worked on it, and kept "Assigned To" as a separate "lead technician" label. This way every technician who touched a job shows up, with their own hours, instead of only the one name the spreadsheet happened to list first.

**Ask the client?**
No — the data itself proves this, we didn't need to guess.
