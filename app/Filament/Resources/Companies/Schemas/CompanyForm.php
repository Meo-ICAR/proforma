<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                    ->label('Email address')
                    ->email(),
                TextInput::make('email_cc')
                    ->email(),
                TextInput::make('email_bcc')
                    ->email(),
                TextInput::make('emailsubject')
                    ->email()
                    ->default('Proforma compensi provvigionali'),
                Textarea::make('compenso_descrizione')
                    ->columnSpanFull(),

            ]);
    }
}
