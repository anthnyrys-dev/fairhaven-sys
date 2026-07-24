<?php

namespace App\Filament\Resources\Technicians\Schemas;

use App\Models\Technician;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TechnicianInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Technician')
                    ->columns(3)
                    ->components([
                        TextEntry::make('name'),
                        TextEntry::make('role')->label('Role')->placeholder('Not recorded'),
                        TextEntry::make('active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive')
                            ->color(fn (bool $state) => $state ? 'success' : 'gray'),
                        TextEntry::make('licence_no')->label('Licence Number')->placeholder('—'),
                        TextEntry::make('start_date')->label('Start Date')->date('d F Y')->placeholder('—'),
                        TextEntry::make('cost_rate')->label('Cost Rate')->money('AUD')->suffix(' / hr')->placeholder('Not recorded'),
                        TextEntry::make('charge_rate')->label('Charge Rate (ex GST)')->money('AUD')->suffix(' / hr')->placeholder('Not recorded'),
                        TextEntry::make('total_hours')
                            ->label('Total Hours Logged')
                            ->state(fn (Technician $record) => number_format($record->totalHours(), 2))
                            ->weight('bold'),
                    ]),
            ]);
    }
}
