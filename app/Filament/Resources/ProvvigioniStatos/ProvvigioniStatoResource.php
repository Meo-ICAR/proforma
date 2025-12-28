<?php

namespace App\Filament\Resources\ProvvigioniStatos;

use App\Filament\Resources\ProvvigioniStatos\Pages\CreateProvvigioniStato;
use App\Filament\Resources\ProvvigioniStatos\Pages\EditProvvigioniStato;
use App\Filament\Resources\ProvvigioniStatos\Pages\ListProvvigioniStatos;
use App\Filament\Resources\ProvvigioniStatos\Pages\ViewProvvigioniStato;
use App\Filament\Resources\ProvvigioniStatos\Schemas\ProvvigioniStatoForm;
use App\Filament\Resources\ProvvigioniStatos\Schemas\ProvvigioniStatoInfolist;
use App\Filament\Resources\ProvvigioniStatos\Tables\ProvvigioniStatosTable;
use App\Models\ProvvigioniStato;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class ProvvigioniStatoResource extends Resource
{
    protected static ?string $model = ProvvigioniStato::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Provvigioni Stato';

    protected static ?string $modelLabel = 'Provvigioni Stato';

    protected static ?string $pluralModelLabel = 'Provvigioni Stato';

    // protected static UnitEnum|string|null $navigationGroup = 'Settings';

    //  protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = false;  // This will hide it from navigation

    protected static ?string $recordTitleAttribute = 'provvigioni_stato';

    public static function form(Schema $schema): Schema
    {
        return ProvvigioniStatoForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProvvigioniStatoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProvvigioniStatosTable::configure($table);
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
            'index' => ListProvvigioniStatos::route('/'),
            'create' => CreateProvvigioniStato::route('/create'),
            //  'view' => ViewProvvigioniStato::route('/{record}'),
            'edit' => EditProvvigioniStato::route('/{record}/edit'),
        ];
    }
}
