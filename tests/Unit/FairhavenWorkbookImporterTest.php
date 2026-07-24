<?php

use App\DataTransferObjects\FairhavenImportResult;
use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Enums\TechnicianRole;
use App\Models\Job;
use App\Models\Technician;
use App\Services\FairhavenWorkbookImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function importer(): FairhavenWorkbookImporter
{
    return new FairhavenWorkbookImporter;
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
    $date = callPrivateMethod(importer(), 'parseDate', ['25/12/2026']);

    expect($date)->toBeInstanceOf(Carbon::class)
        ->and($date->format('Y-m-d'))->toBe('2026-12-25');
});

it('parses Y-m-d dates', function () {
    $date = callPrivateMethod(importer(), 'parseDate', ['2026-12-25']);

    expect($date->format('Y-m-d'))->toBe('2026-12-25');
});

it('parses "d M y" dates', function () {
    $date = callPrivateMethod(importer(), 'parseDate', ['25 Dec 26']);

    expect($date->format('Y-m-d'))->toBe('2026-12-25');
});

it('returns null for blank or unrecognised dates', function () {
    expect(callPrivateMethod(importer(), 'parseDate', [null]))->toBeNull()
        ->and(callPrivateMethod(importer(), 'parseDate', ['']))->toBeNull()
        ->and(callPrivateMethod(importer(), 'parseDate', ['not a date']))->toBeNull();
});

it('parses numeric strings into floats', function () {
    expect(callPrivateMethod(importer(), 'parseDecimal', ['12.5']))->toBe(12.5)
        ->and(callPrivateMethod(importer(), 'parseDecimal', ['0']))->toBe(0.0);
});

it('returns null for blank decimal input', function () {
    expect(callPrivateMethod(importer(), 'parseDecimal', [null]))->toBeNull()
        ->and(callPrivateMethod(importer(), 'parseDecimal', ['']))->toBeNull();
});

it('trims strings and converts blanks to null', function () {
    expect(callPrivateMethod(importer(), 'nullIfBlank', ['  hello  ']))->toBe('hello')
        ->and(callPrivateMethod(importer(), 'nullIfBlank', ['   ']))->toBeNull()
        ->and(callPrivateMethod(importer(), 'nullIfBlank', [null]))->toBeNull();
});

it('maps recognised status strings case-insensitively, defaulting to open', function () {
    expect(callPrivateMethod(importer(), 'matchStatus', ['Completed']))->toBe(JobStatus::COMPLETE)
        ->and(callPrivateMethod(importer(), 'matchStatus', ['in progress']))->toBe(JobStatus::IN_PROGRESS)
        ->and(callPrivateMethod(importer(), 'matchStatus', ['unknown']))->toBe(JobStatus::OPEN)
        ->and(callPrivateMethod(importer(), 'matchStatus', [null]))->toBe(JobStatus::OPEN);
});

it('matches job type labels case-insensitively', function () {
    expect(callPrivateMethod(importer(), 'matchJobType', ['Scheduled Maintenance']))->toBe(JobType::SCHEDULED_MAINTENANCE)
        ->and(callPrivateMethod(importer(), 'matchJobType', ['repair']))->toBe(JobType::REPAIR)
        ->and(callPrivateMethod(importer(), 'matchJobType', ['not a type']))->toBeNull()
        ->and(callPrivateMethod(importer(), 'matchJobType', [null]))->toBeNull();
});

it('matches technician role labels case-insensitively', function () {
    expect(callPrivateMethod(importer(), 'matchRole', ['Senior Technician']))->toBe(TechnicianRole::SENIOR_TECHNICIAN)
        ->and(callPrivateMethod(importer(), 'matchRole', ['technician']))->toBe(TechnicianRole::TECHNICIAN)
        ->and(callPrivateMethod(importer(), 'matchRole', ['apprentice']))->toBeNull()
        ->and(callPrivateMethod(importer(), 'matchRole', [null]))->toBeNull();
});

it('imports the real workbook and returns accurate counts', function () {
    $result = importer()->import(database_path('data/Trial-Task-Data-Fairhaven.xlsx'));

    expect($result)->toBeInstanceOf(FairhavenImportResult::class)
        ->and($result->technicianCount)->toBe(5)
        ->and($result->newTechnicianCount)->toBe(1)
        ->and($result->clientCount)->toBe(8)
        ->and($result->jobCount)->toBe(30)
        ->and($result->legacyJobCount)->toBe(1)
        ->and($result->timeEntryCount)->toBe(50);
});

it('merges abbreviated and full technician names into one canonical record', function () {
    importer()->import(database_path('data/Trial-Task-Data-Fairhaven.xlsx'));

    expect(Technician::where('name', 'Danh Nguyen')->count())->toBe(1);
});

it('is safe to run twice against a clean import each time', function () {
    $path = database_path('data/Trial-Task-Data-Fairhaven.xlsx');

    importer()->import($path);
    $result = importer()->import($path);

    expect($result->technicianCount + $result->newTechnicianCount)->toBe(6)
        ->and(Technician::count())->toBe(6)
        ->and(Job::count())->toBe(31);
});
