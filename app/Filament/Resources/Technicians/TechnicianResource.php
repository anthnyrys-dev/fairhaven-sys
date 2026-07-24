<?php

namespace App\Filament\Resources\Technicians;

use App\Filament\Resources\Technicians\Pages\CreateTechnician;
use App\Filament\Resources\Technicians\Pages\EditTechnician;
use App\Filament\Resources\Technicians\Pages\ListTechnicians;
use App\Filament\Resources\Technicians\Pages\ViewTechnician;
use App\Filament\Resources\Technicians\RelationManagers\TimeEntriesRelationManager;
use App\Filament\Resources\Technicians\Schemas\TechnicianForm;
use App\Filament\Resources\Technicians\Schemas\TechnicianInfolist;
use App\Filament\Resources\Technicians\Tables\TechniciansTable;
use App\Models\Technician;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TechnicianResource extends Resource
{
    protected static ?string $model = Technician::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TechnicianForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TechnicianInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechniciansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TimeEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicians::route('/'),
            'create' => CreateTechnician::route('/create'),
            'view' => ViewTechnician::route('/{record}'),
            'edit' => EditTechnician::route('/{record}/edit'),
        ];
    }
}
