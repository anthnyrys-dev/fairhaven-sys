<?php

use App\Console\Commands\ImportFairhavenWorkbook;
use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Enums\TechnicianRole;
use Illuminate\Support\Carbon;

function importCommand(): ImportFairhavenWorkbook
{
    return new ImportFairhavenWorkbook;
}

/**
 * @param  array<int, mixed>  $args
 */
function callPrivateMethod(object $object, string $method, array $args = []): mixed
{
    $reflection = new ReflectionMethod($object, $method);

    return $reflection->invokeArgs($object, $args);
}

it('parses d/m/Y dates', function () {
    $date = callPrivateMethod(importCommand(), 'parseDate', ['25/12/2026']);

    expect($date)->toBeInstanceOf(Carbon::class)
        ->and($date->format('Y-m-d'))->toBe('2026-12-25');
});

it('parses Y-m-d dates', function () {
    $date = callPrivateMethod(importCommand(), 'parseDate', ['2026-12-25']);

    expect($date->format('Y-m-d'))->toBe('2026-12-25');
});

it('parses "d M y" dates', function () {
    $date = callPrivateMethod(importCommand(), 'parseDate', ['25 Dec 26']);

    expect($date->format('Y-m-d'))->toBe('2026-12-25');
});

it('returns null for blank or unrecognised dates', function () {
    expect(callPrivateMethod(importCommand(), 'parseDate', [null]))->toBeNull()
        ->and(callPrivateMethod(importCommand(), 'parseDate', ['']))->toBeNull()
        ->and(callPrivateMethod(importCommand(), 'parseDate', ['not a date']))->toBeNull();
});

it('parses numeric strings into floats', function () {
    expect(callPrivateMethod(importCommand(), 'parseDecimal', ['12.5']))->toBe(12.5)
        ->and(callPrivateMethod(importCommand(), 'parseDecimal', ['0']))->toBe(0.0);
});

it('returns null for blank decimal input', function () {
    expect(callPrivateMethod(importCommand(), 'parseDecimal', [null]))->toBeNull()
        ->and(callPrivateMethod(importCommand(), 'parseDecimal', ['']))->toBeNull();
});

it('trims strings and converts blanks to null', function () {
    expect(callPrivateMethod(importCommand(), 'nullIfBlank', ['  hello  ']))->toBe('hello')
        ->and(callPrivateMethod(importCommand(), 'nullIfBlank', ['   ']))->toBeNull()
        ->and(callPrivateMethod(importCommand(), 'nullIfBlank', [null]))->toBeNull();
});

it('maps recognised status strings case-insensitively, defaulting to open', function () {
    expect(callPrivateMethod(importCommand(), 'matchStatus', ['Completed']))->toBe(JobStatus::COMPLETE)
        ->and(callPrivateMethod(importCommand(), 'matchStatus', ['in progress']))->toBe(JobStatus::IN_PROGRESS)
        ->and(callPrivateMethod(importCommand(), 'matchStatus', ['unknown']))->toBe(JobStatus::OPEN)
        ->and(callPrivateMethod(importCommand(), 'matchStatus', [null]))->toBe(JobStatus::OPEN);
});

it('matches job type labels case-insensitively', function () {
    expect(callPrivateMethod(importCommand(), 'matchJobType', ['Scheduled Maintenance']))->toBe(JobType::SCHEDULED_MAINTENANCE)
        ->and(callPrivateMethod(importCommand(), 'matchJobType', ['repair']))->toBe(JobType::REPAIR)
        ->and(callPrivateMethod(importCommand(), 'matchJobType', ['not a type']))->toBeNull()
        ->and(callPrivateMethod(importCommand(), 'matchJobType', [null]))->toBeNull();
});

it('matches technician role labels case-insensitively', function () {
    expect(callPrivateMethod(importCommand(), 'matchRole', ['Senior Technician']))->toBe(TechnicianRole::SENIOR_TECHNICIAN)
        ->and(callPrivateMethod(importCommand(), 'matchRole', ['technician']))->toBe(TechnicianRole::TECHNICIAN)
        ->and(callPrivateMethod(importCommand(), 'matchRole', ['apprentice']))->toBeNull()
        ->and(callPrivateMethod(importCommand(), 'matchRole', [null]))->toBeNull();
});
