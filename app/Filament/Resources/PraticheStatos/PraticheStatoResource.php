<?php

namespace App\Filament\Resources\PraticheStatos;

use App\Filament\Resources\PraticheStatos\Pages\CreatePraticheStato;
use App\Filament\Resources\PraticheStatos\Pages\EditPraticheStato;
use App\Filament\Resources\PraticheStatos\Pages\ListPraticheStatos;
use App\Filament\Resources\PraticheStatos\Pages\ViewPraticheStato;
use App\Filament\Resources\PraticheStatos\Schemas\PraticheStatoForm;
use App\Filament\Resources\PraticheStatos\Schemas\PraticheStatoInfolist;
use App\Filament\Resources\PraticheStatos\Tables\PraticheStatosTable;
use App\Models\PraticheStato;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PraticheStatoResource extends Resource
{
    protected static ?string $model = PraticheStato::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Pratiche Stato';
    protected static ?string $modelLabel = 'Pratiche Stato';
    protected static ?string $pluralModelLabel = 'Pratiche Stato';
    protected static UnitEnum|string|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'pratiche_stato';

    public static function form(Schema $schema): Schema
    {
        return PraticheStatoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PraticheStatoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PraticheStatosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPraticheStatos::route('/'),
            'create' => CreatePraticheStato::route('/create'),
            'view' => ViewPraticheStato::route('/{record}'),
            'edit' => EditPraticheStato::route('/{record}/edit'),
        ];
    }
}
