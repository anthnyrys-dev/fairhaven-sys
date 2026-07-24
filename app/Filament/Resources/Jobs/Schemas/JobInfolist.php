<?php

namespace App\Filament\Resources\Jobs\Schemas;

use App\Filament\Resources\Technicians\TechnicianResource;
use App\Models\Job;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Job')
                    ->columns(3)
                    ->components([
                        TextEntry::make('job_no')->label('Job Number'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('job_type')->label('Job Type')->badge()->color('gray')->placeholder('—'),
                        TextEntry::make('date_booked')->label('Date Booked')->date('d F Y'),
                        TextEntry::make('date_completed')->label('Date Completed')->date('d F Y')->placeholder('Not yet completed'),
                        TextEntry::make('amount')->label('Amount (ex GST)')->money('AUD')->placeholder('Not quoted'),
                        TextEntry::make('site_address')->label('Site Address')->placeholder('—'),
                        TextEntry::make('suburb')->placeholder('—'),
                        TextEntry::make('leadTechnician.name')
                            ->label('Lead Technician')
                            ->placeholder('Unassigned')
                            ->url(fn (Job $record) => $record->leadTechnician
                                ? TechnicianResource::getUrl('view', ['record' => $record->leadTechnician])
                                : null),
                        TextEntry::make('description')->columnSpanFull()->placeholder('No description recorded'),
                    ]),
                Section::make('Client')
                    ->columns(3)
                    ->components([
                        TextEntry::make('client.name')->label('Name')->placeholder('Unknown — legacy job'),
                        TextEntry::make('client.phone')
                            ->label('Phone')
                            ->icon('heroicon-o-phone')
                            ->url(fn (?string $state) => $state ? 'tel:'.$state : null)
                            ->copyable()
                            ->copyMessage('Phone number copied')
                            ->placeholder('—'),
                        TextEntry::make('client.email')->label('Email')->placeholder('—'),
                    ]),
                Section::make('Labour')
                    ->description('How this job\'s billed value compares to what it cost in labour.')
                    ->columns(3)
                    ->components([
                        TextEntry::make('total_hours')
                            ->label('Total Hours Logged')
                            ->state(fn (Job $record) => number_format($record->totalHours(), 2)),
                        TextEntry::make('labour_cost')
                            ->label('Labour Cost (at cost rate)')
                            ->state(fn (Job $record) => '$'.number_format($record->labourCost(), 2)),
                        TextEntry::make('margin')
                            ->label('Margin (Amount ex GST − Labour Cost)')
                            ->state(function (Job $record) {
                                if ($record->amount === null) {
                                    return 'Not quoted';
                                }

                                $margin = (float) $record->amount - $record->labourCost();

                                return ($margin >= 0 ? '+' : '−').'$'.number_format(abs($margin), 2);
                            })
                            ->color(function (Job $record) {
                                if ($record->amount === null) {
                                    return 'gray';
                                }

                                return ((float) $record->amount - $record->labourCost()) >= 0 ? 'success' : 'danger';
                            })
                            ->weight('bold'),
                    ]),
            ]);
    }
}
