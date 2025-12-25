<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions;
use Filament\Actions\EditAction;

use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Infolist;

use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry; // ADD THIS
use Filament\Schemas\Schema; // Add this import
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry; // ADD THIS

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
          //  EditAction::make(),
        ];
    }

   public function infolist(Schema $schema): Schema // Update types here
{
    return $schema
        ->schema([
                Section::make('Dettagli Fattura')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('fornitore')
                                    ->label('Fornitore'),
                                TextEntry::make('invoice_number')
                                    ->label('Numero Fattura'),
                                TextEntry::make('invoice_date')
                                    ->date()
                                    ->label('Data Fattura'),
                                TextEntry::make('total_amount')
                                    ->money('EUR')
                                    ->label('Importo Totale'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->label('Stato'),
                                TextEntry::make('isreconiled')
                                    ->label('Riconciliata')
                                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sì' : 'No'),
                            ]),
                    ]),

                Section::make('Proforme Collegate')
                    ->schema([
                        RepeatableEntry::make('relatedProformas')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('id')
                                            ->label('ID Proforma')
                                            ->url(fn ($record) => route('filament.admin.resources.proformas.edit', $record)),
                                        TextEntry::make('fornitore.name')
                                            ->label('Fornitore'),
                                        TextEntry::make('sended_at')
                                            ->dateTime()
                                            ->label('Data Invio'),
                                        TextEntry::make('anticipo')
                                            ->money('EUR')
                                            ->label('Anticipo'),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columns(1)
                            ->columnSpanFull()
                            ->placeholder('Nessuna proforma da riconciliare trovato')
                          //  ->emptyStateHeading('Nessuna proforma collegata trovata')
                           // ->emptyStateDescription('Non sono state trovate proforme che soddisfano i criteri di ricerca.')
                    ])
                    ->collapsible()
                    ->collapsed(false)
            ]);
    }
}
