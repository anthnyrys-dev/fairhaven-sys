<?php

namespace App\DataTransferObjects;

final readonly class FairhavenImportResult
{
    public function __construct(
        public int $technicianCount,
        public int $newTechnicianCount,
        public int $clientCount,
        public int $jobCount,
        public int $legacyJobCount,
        public int $timeEntryCount,
    ) {}
}
