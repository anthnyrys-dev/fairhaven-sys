<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Client Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel()
                    ->prefixIcon('heroicon-o-phone')
                    ->placeholder('08 9221 4408 or 0400 771 830')
                    ->helperText('Formatted automatically, e.g. 0400 771 830.')
                    ->maxLength(50),
                TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
            ]);
    }
}
