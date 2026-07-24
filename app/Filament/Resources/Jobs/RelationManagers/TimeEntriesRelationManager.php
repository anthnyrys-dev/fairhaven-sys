<?php

namespace App\Filament\Resources\Jobs\RelationManagers;

use App\Filament\Resources\Technicians\TechnicianResource;
use App\Filament\Resources\TimeEntries\Schemas\TimeEntryForm;
use App\Models\TimeEntry;
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

    protected static ?string $title = 'Who Worked On This Job';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('technician_id')
                    ->label('Technician')
                    ->relationship('technician', 'name')
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
                TextColumn::make('technician.name')
                    ->label('Technician')
                    ->sortable()
                    ->url(fn (TimeEntry $record) => TechnicianResource::getUrl('view', ['record' => $record->technician])),
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
            ->defaultSort('date')
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
            ->emptyStateHeading('No hours logged yet')
            ->emptyStateDescription('Add a time entry once a technician has worked on this job.');
    }
}
