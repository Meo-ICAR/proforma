<?php

namespace App\Filament\Resources\Provvigiones;

use App\Filament\Resources\Provvigiones\Pages\EditProvvigione;
use App\Filament\Resources\Provvigiones\Pages\ListProvvigiones;
use App\Filament\Resources\Provvigiones\Pages\ListProvvigioniAttive;
use App\Filament\Resources\Provvigiones\Pages\ViewProvvigione;
use App\Filament\Resources\Provvigiones\Schemas\ProvvigioneForm;
use App\Filament\Resources\Provvigiones\Tables\AttiveTable;
use App\Filament\Resources\Provvigiones\Tables\ProvvigionesTable;
use App\Models\Provvigione;
use Filament\Navigation\NavigationItem;  // Add this import at the top
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class ProvvigioneResource extends Resource
{
    protected static ?string $model = Provvigione::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';  // Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Provvigioni';

    protected static ?string $modelLabel = 'Provvigioni';

    protected static ?string $pluralModelLabel = 'Provvigioni';

    // protected static UnitEnum|string|null $navigationGroup = 'Archivi';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'denominazione_riferimento';

    public static function form(Schema $schema): Schema
    {
        return ProvvigioneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProvvigionesTable::configure($table);
    }

    public static function getNavigationItems(): array
    {
        return [
            ...parent::getNavigationItems(),
            NavigationItem::make('Provvigioni Attive')
                ->icon('heroicon-o-check-circle')
                ->url(static::getUrl('attive'))
                ->sort(2),
        ];
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
            'attive' => ListProvvigioniAttive::route('/attive'),
            'view' => ViewProvvigione::route('/{record}'),
            'edit' => EditProvvigione::route('/{record}/edit'),
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
