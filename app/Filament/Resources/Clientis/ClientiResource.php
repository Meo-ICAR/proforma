<?php

namespace App\Filament\Resources\Clientis;

use App\Filament\Resources\Clientis\Pages\CreateClienti;
use App\Filament\Resources\Clientis\Pages\EditClienti;
use App\Filament\Resources\Clientis\Pages\ListClientis;
use App\Filament\Resources\Clientis\Schemas\ClientiForm;
use App\Filament\Resources\Clientis\Tables\ClientisTable;
use App\Models\Clienti;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class ClientiResource extends Resource
{
    protected static ?string $model = Clienti::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Istituti';

    protected static ?string $modelLabel = 'Istituti';

    protected static ?string $pluralModelLabel = 'Istituti';

    // protected static UnitEnum|string|null $navigationGroup = 'Archivi';

    protected static ?int $navigationSort = 8;

    protected static UnitEnum|string|null $navigationGroup = 'Anagrafiche';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ClientiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientisTable::configure($table);
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
            'index' => ListClientis::route('/'),
            'create' => CreateClienti::route('/create'),
            'edit' => EditClienti::route('/{record}/edit'),
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
