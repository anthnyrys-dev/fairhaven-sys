<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Reads one worksheet into plain associative rows, keyed by the slugified
 * heading row (e.g. "Charge Rate (ex GST)" -> "charge_rate_ex_gst") — the
 * same shape the Fairhaven importer expects regardless of which reader
 * produced it.
 */
class SheetToArrayImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, array<string, string|null>> */
    public array $rows = [];

    public function collection(Collection $rows): void
    {
        $this->rows = $rows
            ->map(fn (Collection $row) => $row
                ->map(fn ($value) => $value === null || $value === '' ? null : (string) $value)
                ->all())
            ->all();
    }
}
