<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client Details')
                    ->columns(3)
                    ->components([
                        TextEntry::make('name')->label('Client Name'),
                        TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->url(fn (?string $state) => $state ? 'tel:'.$state : null)
                            ->copyable()
                            ->copyMessage('Phone number copied')
                            ->placeholder('—'),
                        TextEntry::make('email')->placeholder('—'),
                    ]),
            ]);
    }
}
