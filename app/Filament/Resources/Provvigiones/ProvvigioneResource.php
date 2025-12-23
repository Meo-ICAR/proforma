<?php

namespace App\Filament\Resources\Provvigiones;

use App\Filament\Resources\Provvigiones\Pages\CreateProvvigione;
use App\Filament\Resources\Provvigiones\Pages\EditProvvigione;
use App\Filament\Resources\Provvigiones\Pages\ViewProvvigione;
use App\Filament\Resources\Provvigiones\Pages\ListProvvigiones;
use App\Filament\Resources\Provvigiones\Schemas\ProvvigioneForm;
use App\Filament\Resources\Provvigiones\Tables\ProvvigionesTable;
use App\Models\Provvigione;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProvvigioneResource extends Resource
{
    protected static ?string $model = Provvigione::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
       protected static ?string $navigationLabel = 'Provv. Maturate';
    protected static ?string $modelLabel =  'Provvigioni';
    protected static ?string $pluralModelLabel =  'Provvigioni';
   // protected static UnitEnum|string|null $navigationGroup = 'Archivi';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'provvigioni';

    public static function form(Schema $schema): Schema
    {
        return ProvvigioneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProvvigionesTable::configure($table);
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
            'index' => ListProvvigiones::route('/'),
          //  'create' => CreateProvvigione::route('/create'),
            'view' => ViewProvvigione::route('/{record}'),

        ];
    }



    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
