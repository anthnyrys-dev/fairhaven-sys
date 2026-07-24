<?php

namespace App\Filament\Resources\Technicians\RelationManagers;

use App\Filament\Resources\TimeEntries\Schemas\TimeEntryForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimeEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'timeEntries';

    protected static ?string $title = 'Work Done';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                TimeEntryForm::hoursField(),
                TextInput::make('notes')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('notes')
            ->columns([
                TextColumn::make('job.job_no')
                    ->label('Job Number')
                    ->sortable(),
                TextColumn::make('job.client.name')
                    ->label('Client')
                    ->placeholder('—'),
                TextColumn::make('date')
                    ->date('d F Y')
                    ->sortable(),
                TextColumn::make('hours')
                    ->numeric(2)
                    ->suffix(' hrs')
                    ->color(fn ($state) => $state < 0 ? 'danger' : null)
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->numeric(2)),
                TextColumn::make('notes')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->defaultSort('date', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No hours logged yet');
    }
}
