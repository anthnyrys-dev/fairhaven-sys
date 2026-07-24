<?php

namespace App\Console\Commands;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Enums\TechnicianRole;
use App\Imports\FairhavenWorkbookImport;
use App\Models\Client;
use App\Models\Job;
use App\Models\Technician;
use App\Models\TimeEntry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

#[Signature('fairhaven:import {path? : Path to the .xlsx workbook}')]
#[Description('Import the Fairhaven Mechanical Services workbook (Technicians, Jobs, Timesheets) into a clean database')]
class ImportFairhavenWorkbook extends Command
{
    /** @var array<string, Technician> keyed by canonical name */
    private array $techniciansByName = [];

    /** @var array<string, Job> keyed by job_no */
    private array $jobsByNumber = [];

    private const STATUS_MAP = [
        'complete' => JobStatus::COMPLETE,
        'completed' => JobStatus::COMPLETE,
        'done' => JobStatus::COMPLETE,
        'in progress' => JobStatus::IN_PROGRESS,
        'open' => JobStatus::OPEN,
        'scheduled' => JobStatus::SCHEDULED,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Artisan reuses command instances across calls within the same process
        // (e.g. two Artisan::call()s in one test), so this state must not leak.
        $this->techniciansByName = [];
        $this->jobsByNumber = [];

        $path = $this->argument('path') ?? database_path('data/Trial-Task-Data-Fairhaven.xlsx');

        $this->info("Reading workbook: {$path}");
        $workbook = new FairhavenWorkbookImport;
        Excel::import($workbook, $path);

        DB::transaction(function () use ($workbook) {
            $this->resetTables();

            $technicianCount = $this->importTechnicians($workbook->technicians->rows);
            [$clientCount, $jobCount] = $this->importJobs($workbook->jobs->rows);
            [$timeEntryCount, $newTechnicianCount, $legacyJobCount] = $this->importTimesheets($workbook->timesheets->rows);

            $this->newLine();
            $this->info('✅ Import complete:');
            $this->line("  Technicians: {$technicianCount} from staff list".($newTechnicianCount > 0 ? " + {$newTechnicianCount} discovered in timesheets" : ''));
            $this->line("  Clients: {$clientCount}");
            $this->line("  Jobs: {$jobCount}".($legacyJobCount > 0 ? " + {$legacyJobCount} legacy stub(s)" : ''));
            $this->line("  Time entries: {$timeEntryCount}");
        });

        return self::SUCCESS;
    }

    /**
     * Delete in child-to-parent order so this is safe to run against a
     * populated database without needing to touch FK constraint checks.
     */
    private function resetTables(): void
    {
        TimeEntry::query()->delete();
        Job::query()->delete();
        Client::query()->delete();
        Technician::query()->delete();
    }

    /**
     * @param  array<int, array<string, string|null>>  $rows
     */
    private function importTechnicians(array $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $technician = Technician::create([
                'name' => $name,
                'role' => $this->matchRole($row['role'] ?? null),
                'charge_rate' => $this->parseDecimal($row['charge_rate_ex_gst'] ?? null),
                'cost_rate' => $this->parseDecimal($row['cost_rate'] ?? null),
                'start_date' => $this->parseDate($row['start_date'] ?? null),
                'licence_no' => $this->nullIfBlank($row['licence_no'] ?? null),
                'active' => strtoupper(trim((string) ($row['active'] ?? 'Y'))) === 'Y',
            ]);

            $this->techniciansByName[$name] = $technician;
            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, string|null>>  $rows
     * @return array{0: int, 1: int}
     */
    private function importJobs(array $rows): array
    {
        $clientsByEmail = [];
        $jobCount = 0;

        foreach ($rows as $row) {
            $jobNo = trim((string) ($row['job_no'] ?? ''));

            if ($jobNo === '') {
                continue;
            }

            $clientName = trim((string) ($row['client'] ?? ''));
            $email = $this->nullIfBlank($row['email'] ?? null);
            $clientKey = $email ?? $clientName;

            if (! isset($clientsByEmail[$clientKey])) {
                $clientsByEmail[$clientKey] = Client::firstOrCreate(
                    $email ? ['email' => $email] : ['name' => $clientName],
                    [
                        'name' => $clientName,
                        'phone' => $this->nullIfBlank($row['phone'] ?? null),
                        'email' => $email,
                    ]
                );
            }

            $client = $clientsByEmail[$clientKey];

            $assignedName = trim((string) ($row['assigned_to'] ?? ''));
            $leadTechnician = $assignedName !== '' ? ($this->techniciansByName[$assignedName] ?? null) : null;

            $job = Job::create([
                'job_no' => $jobNo,
                'client_id' => $client->id,
                'date_booked' => $this->parseDate($row['date_booked'] ?? null),
                'site_address' => $this->nullIfBlank($row['site_address'] ?? null),
                'suburb' => $this->nullIfBlank($row['suburb'] ?? null),
                'job_type' => $this->matchJobType($row['job_type'] ?? null),
                'description' => $this->nullIfBlank($row['description'] ?? null),
                'lead_technician_id' => $leadTechnician?->id,
                'status' => $this->matchStatus($row['status'] ?? null),
                'date_completed' => $this->parseDate($row['date_completed'] ?? null),
                'amount' => $this->parseDecimal($row['amount_ex_gst'] ?? null),
            ]);

            $this->jobsByNumber[$jobNo] = $job;
            $jobCount++;
        }

        return [count($clientsByEmail), $jobCount];
    }

    /**
     * @param  array<int, array<string, string|null>>  $rows
     * @return array{0: int, 1: int, 2: int}
     */
    private function importTimesheets(array $rows): array
    {
        $entryCount = 0;
        $newTechnicianCount = 0;
        $legacyJobCount = 0;

        foreach ($rows as $row) {
            $jobNo = trim((string) ($row['job_no'] ?? ''));
            $rawName = trim((string) ($row['technician'] ?? ''));
            $hours = $this->parseDecimal($row['hours'] ?? null);

            if ($jobNo === '' || $rawName === '' || $hours === null) {
                continue;
            }

            $job = $this->jobsByNumber[$jobNo] ?? null;

            if ($job === null) {
                $job = Job::create([
                    'job_no' => $jobNo,
                    'client_id' => null,
                    'date_booked' => $this->parseDate($row['date'] ?? null),
                    'description' => 'Legacy job carried over from a prior job book — not present in the Jobs sheet extract.',
                    'status' => JobStatus::OPEN,
                ]);

                $this->jobsByNumber[$jobNo] = $job;
                $legacyJobCount++;
            }

            $technician = $this->resolveTechnician($rawName, $newTechnicianCount);

            TimeEntry::create([
                'work_order_id' => $job->id,
                'technician_id' => $technician->id,
                'date' => $this->parseDate($row['date'] ?? null),
                'hours' => $hours,
                'notes' => $this->nullIfBlank($row['notes'] ?? null),
            ]);

            $entryCount++;
        }

        return [$entryCount, $newTechnicianCount, $legacyJobCount];
    }

    private function matchRole(?string $raw): ?TechnicianRole
    {
        $normalized = strtolower(trim((string) $raw));

        foreach (TechnicianRole::cases() as $case) {
            if (strtolower($case->getLabel()) === $normalized) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Match a timesheet technician name (often abbreviated, e.g. "D. Nguyen")
     * to the canonical staff-list record (e.g. "Danh Nguyen"), creating a new
     * technician only when no staff-list entry corresponds at all.
     */
    private function resolveTechnician(string $rawName, int &$newTechnicianCount): Technician
    {
        if (isset($this->techniciansByName[$rawName])) {
            return $this->techniciansByName[$rawName];
        }

        if (preg_match('/^([A-Za-z])\.\s*(.+)$/', $rawName, $matches) === 1) {
            $initial = $matches[1];
            $surname = trim($matches[2]);

            foreach ($this->techniciansByName as $canonicalName => $technician) {
                $parts = explode(' ', $canonicalName);
                $lastName = end($parts);

                if (strcasecmp($lastName, $surname) === 0 && strcasecmp($canonicalName[0], $initial) === 0) {
                    return $technician;
                }
            }
        }

        $technician = Technician::create([
            'name' => $rawName,
            'active' => true,
        ]);

        $this->techniciansByName[$rawName] = $technician;
        $newTechnicianCount++;

        return $technician;
    }

    private function matchStatus(?string $raw): JobStatus
    {
        $key = strtolower(trim((string) $raw));

        return self::STATUS_MAP[$key] ?? JobStatus::OPEN;
    }

    private function matchJobType(?string $raw): ?JobType
    {
        $normalized = strtolower(trim((string) $raw));

        foreach (JobType::cases() as $case) {
            if (strtolower($case->getLabel()) === $normalized) {
                return $case;
            }
        }

        return null;
    }

    private function parseDate(?string $raw): ?Carbon
    {
        $raw = trim((string) $raw);

        if ($raw === '') {
            return null;
        }

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $raw, $m) === 1) {
            return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
        }

        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $raw, $m) === 1) {
            return Carbon::createFromDate((int) $m[1], (int) $m[2], (int) $m[3])->startOfDay();
        }

        if (preg_match('#^(\d{1,2}) ([A-Za-z]{3}) (\d{2,4})$#', $raw) === 1) {
            return Carbon::createFromFormat('d M y', $raw)->startOfDay();
        }

        return null;
    }

    private function parseDecimal(?string $raw): ?float
    {
        $raw = trim((string) $raw);

        return $raw === '' ? null : (float) $raw;
    }

    private function nullIfBlank(?string $raw): ?string
    {
        $raw = trim((string) $raw);

        return $raw === '' ? null : $raw;
    }
}
