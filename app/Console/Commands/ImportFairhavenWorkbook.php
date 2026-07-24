<?php

namespace App\Console\Commands;

use App\Services\FairhavenWorkbookImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('fairhaven:import {path? : Path to the .xlsx workbook}')]
#[Description('Import the Fairhaven Mechanical Services workbook (Technicians, Jobs, Timesheets) into a clean database')]
class ImportFairhavenWorkbook extends Command
{
    public function __construct(private readonly FairhavenWorkbookImporter $importer)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->argument('path') ?? database_path('data/Trial-Task-Data-Fairhaven.xlsx');

        $this->info("Reading workbook: {$path}");

        $result = $this->importer->import($path);

        $this->newLine();
        $this->info('✅ Import complete:');
        $this->line("  Technicians: {$result->technicianCount} from staff list".($result->newTechnicianCount > 0 ? " + {$result->newTechnicianCount} discovered in timesheets" : ''));
        $this->line("  Clients: {$result->clientCount}");
        $this->line("  Jobs: {$result->jobCount}".($result->legacyJobCount > 0 ? " + {$result->legacyJobCount} legacy stub(s)" : ''));
        $this->line("  Time entries: {$result->timeEntryCount}");

        return self::SUCCESS;
    }
}
