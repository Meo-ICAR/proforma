<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use App\Models\Proforma;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Schema; // Add this import
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RepeatableEntry;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextEntry;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ProformasRelationManager extends RelationManager
{
    protected static string $relationship = 'relatedProformas';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Proforme Collegate';

   public function form(Schema $schema): Schema // Update type hints to Schema
    {
        return $schema
            ->components([
            TextInput::make('id')
                ->required()
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('ID Proforma')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fornitore.name')
                    ->label('Fornitore')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sended_at')
                    ->label('Data Invio')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('anticipo')
                    ->label('Anticipo')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('stato')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Inviata' => 'success',
                        'Inserito' => 'info',
                        'Pagata' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('stato')
                    ->options([
                        'Inserito' => 'Inserito',
                        'Inviata' => 'Inviata',
                        'Pagata' => 'Pagata',
                    ])
                    ->label('Stato'),
            ])
            ->headerActions([
                // Add any header actions if needed
            ])
            ->actions([
               Action::make('view')
                    ->label('')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Proforma $record): string => route('filament.admin.resources.proformas.edit', $record)),
            ])
            ->bulkActions([
                // Add any bulk actions if needed
            ]);
    }

    protected function canCreate(): bool
    {
        return false; // Disable creation from this relation manager
    }

    protected function canEdit(Model $record): bool
    {
        return false; // Disable editing from this relation manager
    }

    protected function canDelete(Model $record): bool
    {
        return false; // Disable deletion from this relation manager
    }
}
