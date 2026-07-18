<?php

namespace App\Filament\Resources\Clientis\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ClientiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Dati Cliente')
                    ->columnSpanFull()
                    ->tabs([

                        // TAB 1: Anagrafica e Recapiti
                        Tab::make('Anagrafica & Recapiti')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('name')
                                        ->label('Ragione Sociale / Name')
                                        ->maxLength(255),

                                    TextInput::make('nome')
                                        ->label('Denominazione fatturazione')
                                        ->maxLength(255),
                                    TextInput::make('abi_name')
                                        ->label('Nome Ufficiale Finanziatore per OAM')
                                        ->maxLength(255),
                                ]),

                                Grid::make(4)->schema([
                                    TextInput::make('piva')
                                        ->label('Partita IVA')
                                        ->maxLength(16),

                                    TextInput::make('cf')
                                        ->label('Codice Fiscale')
                                        ->maxLength(255),

                                    TextInput::make('codice')
                                        ->label('Codice Cliente')
                                        ->maxLength(255),

                                    TextInput::make('coge')
                                        ->label('Conto COGE')
                                        ->maxLength(255),
                                ]),

                                Grid::make(2)->schema([
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),

                                    TextInput::make('website')
                                        ->label('Sito Web')
                                        ->url()
                                        ->maxLength(255),

                                    TextInput::make('regione')
                                        ->label('Regione')
                                        ->maxLength(255),

                                    TextInput::make('citta')
                                        ->label('Città')
                                        ->maxLength(255),
                                ]),
                            ]),

                        // TAB 2: Albi, Registri e Banca
                        Tab::make('Albi e Registri')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                Grid::make(3)->schema([
                                    TextInput::make('type')
                                        ->label('Tipo (Es. Banca, Assicurazione, Utility)')
                                        ->maxLength(30),

                                    TextInput::make('abi')
                                        ->label('Codice ABI / RUI ISVASS')
                                        ->maxLength(30),

                                ]),

                                Fieldset::make('Dati OAM')
                                    ->schema([
                                        TextInput::make('oam')->label('Codice OAM')->maxLength(30),
                                        TextInput::make('oam_name')->label('Denominazione OAM')->maxLength(255),
                                        TextInput::make('numero_iscrizione_rui')->label('Numero Iscrizione')->maxLength(50),
                                        DatePicker::make('oam_at')->label('Data Iscrizione')->native(false),
                                    ])->columns(4),

                                Fieldset::make('Dati IVASS')
                                    ->schema([
                                        TextInput::make('ivass')->label('Codice IVASS')->maxLength(30),
                                        TextInput::make('ivass_name')->label('Denominazione IVASS')->maxLength(255),
                                        Select::make('ivass_section')
                                            ->label('Sezione IVASS')
                                            ->options([
                                                'A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E',
                                            ]),
                                        DatePicker::make('ivass_at')->label('Data Iscrizione')->native(false),
                                    ])->columns(4),
                            ]),

                        // TAB 3: Mandato e Contratti
                        Tab::make('Mandato')
                            ->icon('heroicon-o-document-check')
                            ->schema([
                                Grid::make(4)->schema([
                                    Select::make('status')
                                        ->label('Stato Operativo')
                                        ->options([
                                            'ATTIVO' => 'ATTIVO',
                                            'SCADUTO' => 'SCADUTO',
                                            'RECEDUTO' => 'RECEDUTO',
                                            'SOSPESO' => 'SOSPESO',
                                        ])
                                        ->default('ATTIVO')
                                        ->required(),

                                    TextInput::make('mandate_number')
                                        ->label('Numero Mandato')
                                        ->maxLength(100),

                                    Select::make('principal_type')
                                        ->label('Tipo Mandante')
                                        ->options([
                                            '--' => '--',
                                            'banca' => 'Banca',
                                            'broker' => 'Broker',
                                            'captive' => 'Captive',
                                            'assicurazione' => 'Assicurazione',
                                        ])
                                        ->default('banca')
                                        ->required(),

                                    Select::make('submission_type')
                                        ->label('Modalità Inoltro')
                                        ->options([
                                            '--' => '--',
                                            'accesso portale' => 'Accesso Portale',
                                            'inoltro' => 'Inoltro',
                                            'entrambi' => 'Entrambi',
                                        ])
                                        ->default('accesso portale')
                                        ->required(),
                                ]),

                                Grid::make(4)->schema([
                                    DatePicker::make('stipulated_at')->label('Data Stipula')->native(false),
                                    DatePicker::make('start_date')->label('Data Decorrenza')->native(false),
                                    DatePicker::make('end_date')->label('Data Scadenza')->native(false),
                                    DatePicker::make('dismissed_at')->label('Data Cessazione')->native(false),
                                ]),
                            ]),

                        // TAB 4: Impostazioni, Privacy e Relazioni
                        Tab::make('Impostazioni & Note')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('privacy_contact_email')
                                        ->label('Email Amministrativa')
                                        ->email()
                                        ->maxLength(255),

                                    TextInput::make('dpo_email')
                                        ->label('Email DPO')
                                        ->email()
                                        ->maxLength(255),
                                ]),

                                Textarea::make('notes')
                                    ->label('Note / Provvigioni')
                                    ->columnSpanFull()
                                    ->rows(4),

                                Grid::make(4)->schema([
                                    Toggle::make('is_exclusive')->label('Esclusiva')->default(false),
                                    Toggle::make('is_reported')->label('Accordi Segnalazione')->default(false),
                                    Toggle::make('is_active')->label('Attivo')->default(true),
                                    Toggle::make('is_dummy')->label('Dummy (Fittizio)')->default(false),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
