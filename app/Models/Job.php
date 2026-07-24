<?php

namespace App\Models;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $uuid
 * @property string $job_no
 * @property int|null $client_id
 * @property Carbon|null $date_booked
 * @property string|null $site_address
 * @property string|null $suburb
 * @property JobType|null $job_type
 * @property string|null $description
 * @property int|null $lead_technician_id
 * @property JobStatus $status
 * @property Carbon|null $date_completed
 * @property float|null $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'job_no', 'client_id', 'date_booked', 'site_address', 'suburb', 'job_type',
    'description', 'lead_technician_id', 'status', 'date_completed', 'amount',
])]
class Job extends Model
{
    use HasUuid;

    protected $table = 'work_orders';

    protected function casts(): array
    {
        return [
            'date_booked' => 'date',
            'date_completed' => 'date',
            'amount' => 'decimal:2',
            'job_type' => JobType::class,
            'status' => JobStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<Technician, $this>
     */
    public function leadTechnician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'lead_technician_id');
    }

    /**
     * @return HasMany<TimeEntry, $this>
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'work_order_id');
    }

    public function totalHours(): float
    {
        return (float) $this->timeEntries()->sum('hours');
    }

    public function labourCost(): float
    {
        return (float) $this->timeEntries()
            ->join('technicians', 'technicians.id', '=', 'time_entries.technician_id')
            ->selectRaw('COALESCE(SUM(time_entries.hours * technicians.cost_rate), 0) as total')
            ->value('total');
    }

    public function billableValueAtChargeRate(): float
    {
        return (float) $this->timeEntries()
            ->join('technicians', 'technicians.id', '=', 'time_entries.technician_id')
            ->selectRaw('COALESCE(SUM(time_entries.hours * technicians.charge_rate), 0) as total')
            ->value('total');
    }
}
