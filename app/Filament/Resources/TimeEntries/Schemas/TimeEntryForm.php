<?php

namespace App\Filament\Resources\TimeEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TimeEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('technician_id')
                    ->label('Technician')
                    ->relationship('technician', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('work_order_id')
                    ->label('Job')
                    ->relationship('job', 'job_no')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->required()
                    ->default(now())
                    ->native(false),
                static::hoursField(),
                Textarea::make('notes')
                    ->rows(2)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    /**
     * Shared with the TimeEntries relation managers on Job and Technician,
     * so hours are validated the same way everywhere they can be entered.
     */
    public static function hoursField(): TextInput
    {
        return TextInput::make('hours')
            ->required()
            ->numeric()
            ->minValue(-999.99)
            ->maxValue(999.99)
            ->rule('multiple_of:0.5')
            ->step(0.5)
            ->suffix('hrs')
            ->helperText('Whole or half hours only (e.g. 7 or 7.5). Enter a negative value only to correct a previous entry.')
            ->validationMessages([
                'multiple_of' => 'Hours must be a whole number or a half hour, e.g. 7 or 7.5.',
            ]);
    }
}
