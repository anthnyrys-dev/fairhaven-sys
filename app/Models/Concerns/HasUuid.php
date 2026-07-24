<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Keeps the auto-incrementing integer primary key (fast joins, small
 * indexes, human-readable in logs/DB tools) but exposes an unguessable
 * `uuid` column as the identifier used in panel URLs, so record IDs can't
 * be enumerated by walking the number line.
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->uuid)) {
                $model->uuid = (string) Str::uuid7();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
