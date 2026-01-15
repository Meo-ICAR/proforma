<?php

namespace App\Filament\Resources\Fatturas;

use App\Filament\Resources\Fatturas\Pages\CreateFattura;
use App\Filament\Resources\Fatturas\Pages\EditFattura;
use App\Filament\Resources\Fatturas\Pages\ListFatturas;
use App\Filament\Resources\Fatturas\Schemas\FatturaForm;
use App\Filament\Resources\Fatturas\Tables\FatturasTable;
use App\Models\Fattura;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class FatturaResource extends Resource
{
    protected static ?string $model = Fattura::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Provv. Maturate';

    protected static ?string $modelLabel = 'Maturate';

    protected static ?string $pluralModelLabel = 'Maturate';

    // protected static UnitEnum|string|null $navigationGroup = 'Archivi';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return FatturaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FatturasTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return false;
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
            'index' => ListFatturas::route('/'),
            'create' => CreateFattura::route('/create'),
            'edit' => EditFattura::route('/{record}/edit'),
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
