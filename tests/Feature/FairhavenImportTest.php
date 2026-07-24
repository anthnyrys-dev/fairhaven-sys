<?php

use App\Models\Job;
use App\Models\Technician;
use App\Models\TimeEntry;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('fairhaven:import');
});

it('merges abbreviated and full technician names into one canonical record', function () {
    $matches = Technician::where('name', 'Danh Nguyen')->get();

    expect($matches)->toHaveCount(1);

    $danh = $matches->first();
    $hoursFromTimesheets = TimeEntry::where('technician_id', $danh->id)->sum('hours');

    expect((float) $hoursFromTimesheets)->toBeGreaterThan(0);
});

it('creates a technician for a name missing from the staff list, without fabricating rates', function () {
    $fitzsimmons = Technician::where('name', 'J. Fitzsimmons')->first();

    expect($fitzsimmons)->not->toBeNull()
        ->and($fitzsimmons->charge_rate)->toBeNull()
        ->and($fitzsimmons->cost_rate)->toBeNull();
});

it('keeps a job with more than one technician linked to all of them', function () {
    $job = Job::where('job_no', 'J-3001')->firstOrFail();

    $technicianNames = $job->timeEntries()->with('technician')->get()
        ->pluck('technician.name')
        ->unique()
        ->sort()
        ->values();

    expect($technicianNames->all())->toBe(['Danh Nguyen', 'Tom Wallace']);
});

it('creates a legacy stub job for a timesheet row with no matching job in the Jobs sheet', function () {
    $job = Job::where('job_no', 'J-2988')->first();

    expect($job)->not->toBeNull()
        ->and($job->client_id)->toBeNull();

    $hours = TimeEntry::where('work_order_id', $job->id)->sum('hours');
    expect((float) $hours)->toBe(4.0);
});

it('nets the self-cancelling correction entry to zero without deleting either row', function () {
    $job = Job::where('job_no', 'J-3022')->firstOrFail();
    $raman = Technician::where('name', 'Priya Raman')->firstOrFail();

    $entries = TimeEntry::where('work_order_id', $job->id)
        ->where('technician_id', $raman->id)
        ->get();

    expect($entries)->toHaveCount(2)
        ->and((float) $entries->sum('hours'))->toBe(0.0);
});

it('is safe to run twice against a clean import each time', function () {
    Artisan::call('fairhaven:import');

    expect(Technician::count())->toBe(6)
        ->and(Job::count())->toBe(31);
});
