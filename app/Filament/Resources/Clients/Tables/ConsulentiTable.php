<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Filament\Resources\PrimaNotaEntries\PrimaNotaEntryResource;
use App\Models\Client;
use App\Models\ClientType;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ConsulentiTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Client::query()->where('is_company', 1))
            ->columns([
                // Identificazione Rapida
                TextColumn::make('name')  // Presuppone un accessor nel modello o usa formatStateUsing
                    ->label('Consulente/Fornitore')
                    ->sortable()
                    ->searchable(),
                // Partita IVA per consulenti/fornitori
                TextColumn::make('vat_number')
                    ->label('P.IVA')
                    ->searchable()
                    ->placeholder('N/D')
                    ->toggleable(),
                // Email di contatto
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copiata!')
                    ->copyMessageDuration(1500)
                    ->toggleable(),
                // Telefono
                TextColumn::make('phone')
                    ->label('Telefono')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Telefono copiato!')
                    ->copyMessageDuration(1500)
                    ->toggleable(isToggledHiddenByDefault: true),
                // Tipo Consulente
                TextColumn::make('clientType.name')
                    ->label('Tipo Consulente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('N/D')
                    ->toggleable(),
                IconColumn::make('privacy_consent')
                    ->label('Privacy')
                    ->boolean()
                    ->trueIcon('heroicon-s-shield-check')
                    ->falseIcon('heroicon-o-shield-check')
                    ->color(fn ($state) => $state ? 'success' : 'warning')
                    ->tooltip(fn ($record) => $record->privacy_policy_read_at
                        ? 'Privacy sottoscritta: '.$record->privacy_policy_read_at->format('d/m/Y')
                        : 'Privacy da firmare'),
            ])
            ->filters([
                // Filtro per tipologia
                // Filtro per Tipo Consulente
                SelectFilter::make('client_type_id')
                    ->label('Tipo Consulente')
                    ->placeholder('Tutti')
                    ->options(function () {
                        return ClientType::pluck('name', 'id')->sort()->toArray();
                    })
                    ->searchable()
                    ->multiple(),
                // Filtro per Privacy
                TernaryFilter::make('privacy_consent')
                    ->label('Nomina Privacy'),
            ])
            ->recordActions([
                Action::make('primanota')
                    ->label('Contabile')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn ($record) => PrimaNotaEntryResource::getUrl('index').'?filters[client_id][value]='.$record->id)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                //  BulkActionGroup::make([
                //      DeleteBulkAction::make(),
                //  ]),
            ])
            ->defaultSort('name');
    }
}
