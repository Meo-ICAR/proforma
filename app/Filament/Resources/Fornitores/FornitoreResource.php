<?php

namespace App\Filament\Resources\Fornitores;

use App\Filament\Resources\Fornitores\Pages\CreateFornitore;
use App\Filament\Resources\Fornitores\Pages\EditFornitore;
use App\Filament\Resources\Fornitores\Pages\ViewFornitore;
use App\Filament\Resources\Fornitores\Pages\ListFornitores;
use App\Filament\Resources\Fornitores\Schemas\FornitoreForm;
use App\Filament\Resources\Fornitores\Schemas\FornitoreInfoList;
use App\Filament\Resources\Fornitores\Tables\FornitoresTable;
use App\Models\Fornitore;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FornitoreResource extends Resource
{
    protected static ?string $model = Fornitore::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Agenti';
    protected static ?string $modelLabel =  'Agenti';
    protected static ?string $pluralModelLabel =  'Agenti';
    protected static UnitEnum|string|null $navigationGroup = 'Archivi';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'Agenti';

    public static function form(Schema $schema): Schema
    {
        return FornitoreForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FornitoreInfoList::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FornitoresTable::configure($table);
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
            'index' => ListFornitores::route('/'),
            'create' => CreateFornitore::route('/create'),
               'view' => ViewFornitore::route('/{record}'),
            'edit' => EditFornitore::route('/{record}/edit'),
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
