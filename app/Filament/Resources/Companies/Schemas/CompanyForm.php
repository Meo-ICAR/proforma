<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('piva'),
                TextInput::make('email')
                    ->label('Email mittente proforma')
                    ->email(),
                TextInput::make('email_cc')
                    ->label('Email collaboratore in cc ai proforma')
                    ->email(),
                TextInput::make('email_bcc')
                    ->label('Email collaboratore in copia nascosca ai proforma')
                    ->email(),
                TextInput::make('emailsubject')
                    ->label('Intestazione compensi provvigionali')
                    ->default('Proforma compensi provvigionali'),
                Textarea::make('compenso_descrizione')
                    ->columnSpanFull(),
            ]);
    }
}
