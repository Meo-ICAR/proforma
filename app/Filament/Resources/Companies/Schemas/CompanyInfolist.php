<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Models\Company;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                TextEntry::make('name'),
                TextEntry::make('piva')
                    ->placeholder('-'),

                TextEntry::make('email')
                    ->label('Email address')
                    ->placeholder('-'),
                TextEntry::make('email_cc')
                    ->placeholder('-'),
                TextEntry::make('email_bcc')
                    ->placeholder('-'),
                TextEntry::make('emailsubject')
                    ->placeholder('-'),
                TextEntry::make('compenso_descrizione')
                    ->placeholder('-')
                    ->columnSpanFull(),

            ]);
    }
}
