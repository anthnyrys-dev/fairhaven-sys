<?php

namespace App\Filament\Resources\Clients\RelationManagers;

use App\Enums\JobStatus;
use App\Enums\JobType;
use App\Filament\Resources\Jobs\JobResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobsRelationManager extends RelationManager
{
    protected static string $relationship = 'jobs';

    protected static ?string $title = 'Job History';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('job_no')
                    ->label('Job Number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->regex('/^J-\d{3,}$/')
                    ->maxLength(20),
                Select::make('status')
                    ->options(JobStatus::class)
                    ->required()
                    ->default(JobStatus::OPEN->value)
                    ->native(false),
                Select::make('job_type')
                    ->label('Job Type')
                    ->options(JobType::class)
                    ->native(false),
                Select::make('lead_technician_id')
                    ->label('Lead Technician')
                    ->relationship('leadTechnician', 'name', fn ($query) => $query->where('active', true))
                    ->searchable()
                    ->preload(),
                DatePicker::make('date_booked')->label('Date Booked')->default(now())->native(false),
                DatePicker::make('date_completed')->label('Date Completed')->afterOrEqual('date_booked')->native(false),
                TextInput::make('amount')->label('Amount (ex GST)')->numeric()->prefix('$')->minValue(0)->maxValue(99_999_999.99),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('job_no')
            ->columns([
                TextColumn::make('job_no')->label('Job Number')->badge()->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('date_booked')->label('Date Booked')->date('d F Y')->sortable(),
                TextColumn::make('date_completed')->label('Date Completed')->date('d F Y')->placeholder('—')->sortable(),
                TextColumn::make('amount')->label('Amount (ex GST)')->money('AUD')->placeholder('Not quoted'),
            ])
            ->defaultSort('date_booked', 'desc')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()->url(fn ($record) => JobResource::getUrl('view', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No jobs for this client yet');
    }
}
