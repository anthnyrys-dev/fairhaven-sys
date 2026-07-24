<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FairhavenWorkbookImport implements WithMultipleSheets
{
    public SheetToArrayImport $technicians;

    public SheetToArrayImport $jobs;

    public SheetToArrayImport $timesheets;

    public function __construct()
    {
        $this->technicians = new SheetToArrayImport;
        $this->jobs = new SheetToArrayImport;
        $this->timesheets = new SheetToArrayImport;
    }

    /**
     * @return array<string, SheetToArrayImport>
     */
    public function sheets(): array
    {
        return [
            'Technicians' => $this->technicians,
            'Jobs' => $this->jobs,
            'Timesheets' => $this->timesheets,
        ];
    }
}
