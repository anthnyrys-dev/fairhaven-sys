<?php

namespace App\Filament\Resources\Jobs\Tables;

use App\Enums\JobStatus;
use App\Enums\JobType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('job_no')
                    ->label('Job Number')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No client on record'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('job_type')
                    ->label('Job Type')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('leadTechnician.name')
                    ->label('Lead Technician')
                    ->placeholder('Unassigned')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date_booked')
                    ->label('Date Booked')
                    ->date('d F Y')
                    ->sortable(),
                TextColumn::make('date_completed')
                    ->label('Date Completed')
                    ->date('d F Y')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount (ex GST)')
                    ->money('AUD')
                    ->placeholder('Not quoted')
                    ->sortable(),
            ])
            ->defaultSort('date_booked', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(JobStatus::class),
                SelectFilter::make('job_type')
                    ->label('Job Type')
                    ->options(JobType::class),
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
            ->emptyStateHeading('No jobs yet')
            ->emptyStateDescription('Jobs booked in for clients will appear here.');
    }
}
