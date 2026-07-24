<?php

namespace App\Filament\Resources\Technicians\Schemas;

use App\Enums\TechnicianRole;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TechnicianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Technician')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('role')
                            ->label('Role')
                            ->options(TechnicianRole::class),
                        TextInput::make('licence_no')
                            ->label('Licence Number')
                            ->maxLength(255),
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->native(false),
                        Toggle::make('active')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
                Section::make('Rates')
                    ->description('Charge Rate is what the client is billed per hour. Cost Rate is what the technician costs the business per hour.')
                    ->columns(2)
                    ->components([
                        TextInput::make('cost_rate')
                            ->label('Cost Rate')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('/ hr')
                            ->minValue(0)
                            ->maxValue(999_999.99),
                        TextInput::make('charge_rate')
                            ->label('Charge Rate (ex GST)')
                            ->numeric()
                            ->prefix('$')
                            ->suffix('/ hr')
                            ->minValue(0)
                            ->maxValue(999_999.99)
                            ->gte('cost_rate')
                            ->validationMessages([
                                'gte' => 'Charge rate should not be less than the cost rate.',
                            ]),
                    ]),
            ]);
    }
}
