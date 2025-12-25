<?php

namespace App\Filament\Resources\Praticas\RelationManagers;

use Filament\Actions\EditAction;
//use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;

class ProvvigioniRelationManager extends RelationManager
{
    protected static string $relationship = 'provvigioni';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('segnalatore'),
                TextInput::make('importo'),
              //  ->money('EUR')
               // ->alignEnd()
                TextInput::make('descrizione')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('entrata_uscita'),
                TextEntry::make('segnalatore'),
                TextEntry::make('importo')
                ->money('EUR')
                ->alignEnd(),
                TextEntry::make('descrizione'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Provvigioni associate alla pratica')
            ->columns([
                 TextColumn::make('entrata_uscita'),
                  TextColumn::make('segnalatore'),
                        TextColumn::make('importo')
                        ->money('EUR')
                        ->alignEnd(),
                TextColumn::make('descrizione'),
                 TextColumn::make('quota')
                 ->label('Storno')
                 ->money('EUR')
                 ->alignEnd(),
                  //  ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
              //  CreateAction::make(),
              //   AssociateAction::make(),
            ])
            ->recordActions([
             //   ViewAction::make(),
                Action::make('storna')
                    ->label('Storna')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (\App\Models\Provvigione $record): bool =>
                        $record->entrata_uscita === 'Entrata' &&
                        (!isset($record->quota) || $record->quota > 0)
                    )
                    ->action(function (Model $record) {
                        // Add your storno logic here
                        // For example:
                        // $record->update(['entrata_uscita' => 'Storno']);
                        // or any other storno logic you need
                    }),
              //  EditAction::make(),
              //  DissociateAction::make(),
               // DeleteAction::make(),
            ])
           ;
    }
}
