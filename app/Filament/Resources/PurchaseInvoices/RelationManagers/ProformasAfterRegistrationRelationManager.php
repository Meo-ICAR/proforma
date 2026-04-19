<?php

namespace App\Filament\Resources\PurchaseInvoices\RelationManagers;

use App\Models\Proforma;
use App\Models\PurchaseInvoice;
use Filament\Actions\Action;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ProformasAfterRegistrationRelationManager extends RelationManager
{
    protected static string $relationship = 'proformasAfterRegistration';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('emailsubject')
                    ->disabled()
                    ->columnSpanFull(),
                DateTimePicker::make('sended_at')
                    ->disabled(),
                TextInput::make('compenso')
                    ->disabled()
                    ->numeric(),
                TextInput::make('contributo')
                    ->disabled()
                    ->numeric(),
                TextInput::make('anticipo')
                    ->disabled()
                    ->numeric(),
                Textarea::make('annotation')
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('delta')
                    ->label('Differenza con fattura')
                    ->live()
                    ->numeric(),
                Textarea::make('delta_annotation')
                    ->label('Giustificativo differenza')
                    ->required(fn($get) => $get('delta') != 0)
                    ->columnSpanFull(),
                TextInput::make('id')->disabled(),
                TextInput::make('invoiceable_id')->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->checkIfRecordIsSelectableUsing(
                fn(Proforma $record): bool => $record->invoiceable_id === null
            )
            ->recordTitleAttribute('name')
            ->defaultSort('sended_at', 'desc')
            ->columns([
                TextColumn::make('sended_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('totale')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('compenso')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->summarize(Sum::make())
                    ->sortable(),
                TextColumn::make('contributo')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('anticipo')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('stato')
                    ->searchable(),
                TextColumn::make('delta')
                    ->money('EUR')  // Forza Euro e formato italiano
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('emailsubject')
                    ->searchable(),
                TextColumn::make('purchaseInvoice.sended_at')
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
                            ->when($data['sended_from'], fn(Builder $query, $date) => $query->where('sended_at', '>=', $date))
                            ->when($data['sended_to'], fn(Builder $query, $date) => $query->where('sended_at', '<=', $date));
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
                    ->label('Riconcilia Proforma con fattura')
                    ->color('success')
                    ->accessSelectedRecords()
                    ->before(function (BulkAction $action, Collection $records) {
                        $purchaseInvoice = $this->getOwnerRecord();
                        $purchaseInvoiceId = $purchaseInvoice->id;
                        $purchaseAmount = $purchaseInvoice->amount;
                        $sum = $records->sum('totale');
                        $delta = $sum - $purchaseAmount;
                        if (abs($delta) > 5) {
                            Notification::make()
                                ->warning()
                                ->title('Differenza importi troppo grande!')
                                ->body('Totale proforma ' . $sum . ' non corrisponde al totale della fattura ' . $purchaseAmount . ' (delta: ' . $delta . '). Modifica il delta su proforma e riprovare.')
                                ->persistent()
                                ->send();

                            $action->halt();
                        }
                    })
                    //  ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        // Process each record with a visible loop
                        $records->each(function ($record) use ($purchaseInvoiceId) {
                            $record->update([
                                'invoiceable_type' => 'App\Models\PurchaseInvoice',
                                'invoiceable_id' => $purchaseInvoiceId
                            ]);
                        });

                        // Show success notification with count
                        Notification::make()
                            ->title(count($records) . ' proforme riconciliate con fattura')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}
