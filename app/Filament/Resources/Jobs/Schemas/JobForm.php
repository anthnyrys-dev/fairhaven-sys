<?php

namespace App\Filament\Resources\Jobs\Schemas;

use App\Enums\JobStatus;
use App\Enums\JobType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Job')
                    ->columns(2)
                    ->components([
                        TextInput::make('job_no')
                            ->label('Job Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->regex('/^J-\d{3,}$/')
                            ->helperText('Format: J-3001')
                            ->maxLength(20),
                        Select::make('status')
                            ->options(JobStatus::class)
                            ->required()
                            ->default(JobStatus::OPEN->value)
                            ->native(false),
                        Select::make('client_id')
                            ->label('Client')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('phone')->tel()->prefixIcon('heroicon-o-phone'),
                                TextInput::make('email')->email(),
                            ]),
                        Select::make('lead_technician_id')
                            ->label('Lead Technician')
                            ->relationship('leadTechnician', 'name', fn ($query) => $query->where('active', true))
                            ->searchable()
                            ->preload(),
                        Select::make('job_type')
                            ->label('Job Type')
                            ->options(JobType::class)
                            ->native(false),
                        TextInput::make('amount')
                            ->label('Amount (ex GST)')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->maxValue(99_999_999.99),
                        DatePicker::make('date_booked')
                            ->label('Date Booked')
                            ->default(now())
                            ->native(false),
                        DatePicker::make('date_completed')
                            ->label('Date Completed')
                            ->afterOrEqual('date_booked')
                            ->native(false),
                    ]),
                Section::make('Site')
                    ->columns(2)
                    ->components([
                        TextInput::make('site_address')->label('Site Address')->maxLength(255),
                        TextInput::make('suburb')->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
