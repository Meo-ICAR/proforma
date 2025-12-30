<?php

namespace App\Filament\Resources\Vcoges;

use App\Filament\Resources\Vcoges\Pages\ListVcoges;
use App\Filament\Resources\Vcoges\Schemas\VcogeForm;
use App\Filament\Resources\Vcoges\Schemas\VcogeInfolist;
use App\Filament\Resources\Vcoges\Tables\VcogesTable;
use App\Models\Vcoge;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;

class VcogeResource extends Resource
{
    protected static ?string $model = Vcoge::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static bool $shouldRegisterNavigation = false;  // This will hide it from navigation

    protected static ?string $navigationLabel = 'Prospetto provvigionale mensile';

    protected static ?string $modelLabel = 'Prospetto provvigionale mensile';

    protected static ?string $pluralModelLabel = 'Prospetto provvigionale mensile';

    protected static ?string $recordTitleAttribute = 'mese';

    public static function form(Schema $schema): Schema
    {
        return VcogeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VcogeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VcogesTable::configure($table);
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
            'index' => ListVcoges::route('/'),
            //  'create' => CreateVcoge::route('/create'),
            //  'view' => ViewVcoge::route('/{record}'),
            //  'edit' => EditVcoge::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('mese', 'desc');
    }
}
