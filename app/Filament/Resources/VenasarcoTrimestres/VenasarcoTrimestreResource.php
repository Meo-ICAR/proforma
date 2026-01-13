<?php

namespace App\Filament\Resources\VenasarcoTrimestres;

use App\Filament\Resources\VenasarcoTrimestres\Pages\ListVenasarcoTrimestres;
use App\Filament\Resources\VenasarcoTrimestres\Pages\ViewVenasarcoTrimestre;
use App\Filament\Resources\VenasarcoTrimestres\Schemas\VenasarcoTrimestreInfolist;
use App\Filament\Resources\VenasarcoTrimestres\Tables\VenasarcoTrimestresTable;
use App\Models\VenasarcoTrimestre;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class VenasarcoTrimestreResource extends Resource
{
    protected static ?string $model = VenasarcoTrimestre::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static UnitEnum|string|null $navigationGroup = null;  // Or set to false to hide completely

    protected static ?int $navigationSort = null;  // Optional: remove from sort order

    protected static ?string $navigationLabel = 'Contributi trimestrali ENASARCO';

    protected static ?string $modelLabel = 'Trimestrali ENASARCO';

    protected static ?string $pluralModelLabel = 'Trimestrali ENASARCO';

    protected static bool $shouldRegisterNavigation = false;  // This will hide it from navigation

    protected static ?string $recordTitleAttribute = 'Trimestre';

    public static function form(Schema $schema): Schema
    {
        return VenasarcoTrimestreForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VenasarcoTrimestreInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenasarcoTrimestresTable::configure($table);
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
            'index' => ListVenasarcoTrimestres::route('/'),
        ];
    }
}
