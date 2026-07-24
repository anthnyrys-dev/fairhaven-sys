<?php

namespace App\Models;

use App\Enums\TechnicianRole;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property TechnicianRole|null $role
 * @property float|null $charge_rate
 * @property float|null $cost_rate
 * @property Carbon|null $start_date
 * @property string|null $licence_no
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'role', 'charge_rate', 'cost_rate', 'start_date', 'licence_no', 'active'])]
class Technician extends Model
{
    use HasUuid;

    protected function casts(): array
    {
        return [
            'role' => TechnicianRole::class,
            'charge_rate' => 'decimal:2',
            'cost_rate' => 'decimal:2',
            'start_date' => 'date',
            'active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Job, $this>
     */
    public function leadJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'lead_technician_id');
    }

    /**
     * @return HasMany<TimeEntry, $this>
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function totalHours(): float
    {
        return (float) $this->timeEntries()->sum('hours');
    }
}
