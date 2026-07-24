<?php

namespace App\Filament\Resources\TimeEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date('d F Y')
                    ->sortable(),
                TextColumn::make('technician.name')
                    ->label('Technician')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('job.job_no')
                    ->label('Job Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('job.client.name')
                    ->label('Client')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('hours')
                    ->numeric(2)
                    ->suffix(' hrs')
                    ->color(fn ($state) => $state < 0 ? 'danger' : null)
                    ->sortable(),
                TextColumn::make('notes')
                    ->placeholder('—')
                    ->limit(40)
                    ->searchable(),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hours logged yet')
            ->emptyStateDescription('Weekly timesheets entered by technicians will appear here.');
    }
}
