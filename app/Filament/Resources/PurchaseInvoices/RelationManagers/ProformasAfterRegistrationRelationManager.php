<?php

namespace App\Filament\Resources\PurchaseInvoices\RelationManagers;

use App\Models\Proforma;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProformasAfterRegistrationRelationManager extends RelationManager
{
    protected static string $relationship = 'proformasAfterRegistration';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('emailsubject')
                    ->label('Oggetto email')
                    ->disabled()
                    ->columnSpanFull(),
                DateTimePicker::make('sended_at')
                    ->label('Data invio')
                    ->disabled(),
                TextInput::make('compenso')
                    ->label('Compenso')
                    ->disabled()
                    ->numeric(),
                TextInput::make('contributo')
                    ->label('Contributo')
                    ->disabled()
                    ->numeric(),
                TextInput::make('anticipo')
                    ->label('Anticipo')
                    ->disabled()
                    ->numeric(),
                Textarea::make('annotation')
                    ->label('Note')
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('delta')
                    ->label('Differenza con fattura')
                    ->live()
                    ->numeric(),
                Textarea::make('delta_annotation')
                    ->label('Giustificativo differenza')
                    ->required(fn ($get) => $get('delta') != 0)
                    ->columnSpanFull(),
                TextInput::make('id')
                    ->label('ID')
                    ->disabled(),
                TextInput::make('invoiceable_id')
                    ->label('ID fattura abbinata')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->checkIfRecordIsSelectableUsing(
                fn (Proforma $record): bool => $record->invoiceable_id === null
            )
            ->recordTitleAttribute('name')
            ->defaultSort('sended_at', 'desc')
            ->columns([
                TextColumn::make('sended_at')
                    ->label('Data invio')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('totale')
                    ->label('Totale')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('compenso')
                    ->label('Compenso')
                    ->money('EUR')
                    ->alignEnd()
                    ->summarize(Sum::make()->label('Totale'))
                    ->sortable(),
                TextColumn::make('contributo')
                    ->label('Contributo')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('anticipo')
                    ->label('Anticipo')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('stato')
                    ->label('Stato')
                    ->searchable(),
                TextColumn::make('delta')
                    ->label('Differenza')
                    ->money('EUR')
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('emailsubject')
                    ->label('Oggetto email')
                    ->searchable(),
                TextColumn::make('purchaseInvoice.sended_at')
                    ->label('Data fattura abbinata'),
            ])
            ->filters([
                Filter::make('sended_at_range')
                    ->label('Intervallo date invio')
                    ->form([
                        DateTimePicker::make('sended_from')
                            ->label('Da'),
                        DateTimePicker::make('sended_to')
                            ->label('A'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['sended_from'], fn (Builder $query, $date) => $query->where('sended_at', '>=', $date))
                            ->when($data['sended_to'], fn (Builder $query, $date) => $query->where('sended_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): string {
                        if ($data['sended_from'] && $data['sended_to']) {
                            return "Da: {$data['sended_from']} A: {$data['sended_to']}";
                        }
                        if ($data['sended_from']) {
                            return "Da: {$data['sended_from']}";
                        }
                        if ($data['sended_to']) {
                            return "A: {$data['sended_to']}";
                        }

                        return '';
                    }),
            ])
            ->headerActions([
                BulkAction::make('riconcilia')
                    ->label('Riconcilia proforma con fattura')
                    ->color('success')
                    ->accessSelectedRecords()
                    ->before(function (BulkAction $action, Collection $records) {
                        $purchaseInvoice = $this->getOwnerRecord();
                        $purchaseAmount = $purchaseInvoice->amount;
                        $sum = $records->sum('totale');
                        $delta = $sum - $purchaseAmount;
                        if (abs($delta) > 5) {
                            Notification::make()
                                ->warning()
                                ->title('Differenza importi troppo grande!')
                                ->body('Totale proforma '.$sum.' non corrisponde al totale della fattura '.$purchaseAmount.' (differenza: '.$delta.'). Modifica la differenza sulla proforma e riprova.')
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    })
                    ->action(function (Collection $records) {
                        $purchaseInvoice = $this->getOwnerRecord();
                        $purchaseInvoiceId = $purchaseInvoice->id;

                        $records->each(function ($record) use ($purchaseInvoiceId) {
                            $record->update([
                                'invoiceable_type' => 'App\Models\PurchaseInvoice',
                                'invoiceable_id' => $purchaseInvoiceId,
                            ]);
                            $record->provvigioni()->where('proforma_id', $record->id)->update([
                                'stato' => 'Pagato',
                            ]);
                        });
                        $purchaseInvoice->update([
                            'closed' => true,
                        ]);
                        Notification::make()
                            ->title(count($records).' proforma riconciliate con la fattura')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifica'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make()
                        ->label('Dissocia selezionati'),
                ]),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $query->withoutGlobalScopes([SoftDeletingScope::class]);

                $purchaseInvoice = $this->getOwnerRecord();

                // Se la fattura è già riconciliata mostra solo le proforma ad essa abbinate
                if ($purchaseInvoice->closed) {
                    $query->where('invoiceable_type', \App\Models\PurchaseInvoice::class)
                          ->where('invoiceable_id', $purchaseInvoice->id);
                }
            });
    }
}
