<?php

namespace App\Filament\Resources\Technicians\Tables;

use App\Enums\TechnicianRole;
use App\Models\Technician;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TechniciansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->placeholder('Not recorded')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('cost_rate')
                    ->label('Cost Rate')
                    ->money('AUD')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('charge_rate')
                    ->label('Charge Rate')
                    ->money('AUD')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('total_hours')
                    ->label('Total Hours')
                    ->state(fn (Technician $record) => number_format($record->totalHours(), 2))
                    ->sortable(false),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('active'),
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(TechnicianRole::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No technicians yet');
    }
}
