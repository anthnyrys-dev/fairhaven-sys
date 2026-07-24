<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->icon('heroicon-o-phone')
                    ->placeholder('—')
                    ->url(fn (?string $state) => $state ? 'tel:'.$state : null)
                    ->copyable()
                    ->copyMessage('Phone number copied')
                    ->searchable(),
                TextColumn::make('email')
                    ->placeholder('—')
                    ->badge()
                    ->searchable(),
                TextColumn::make('jobs_count')
                    ->label('Jobs')
                    ->counts('jobs')
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No clients yet')
            ->emptyStateDescription('Clients are created automatically when a job is booked for them.');
    }
}
