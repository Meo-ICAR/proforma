<?php

namespace App\Filament\Resources\PrimaNotaConfigs;

use App\Filament\Resources\PrimaNotaConfigs\Pages\CreatePrimaNotaConfig;
use App\Filament\Resources\PrimaNotaConfigs\Pages\EditPrimaNotaConfig;
use App\Filament\Resources\PrimaNotaConfigs\Pages\ListPrimaNotaConfigs;
use App\Filament\Resources\PrimaNotaConfigs\Schemas\PrimaNotaConfigForm;
use App\Filament\Resources\PrimaNotaConfigs\Tables\PrimaNotaConfigsTable;
use App\Models\PrimaNotaConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PrimaNotaConfigResource extends Resource
{
    protected static ?string $model = PrimaNotaConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Regole Prima Nota';

    protected static ?string $modelLabel = 'Regola Prima Nota';

    protected static ?string $pluralModelLabel = 'Regole Prima Nota';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PrimaNotaConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrimaNotaConfigsTable::configure($table);
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
            'index' => ListPrimaNotaConfigs::route('/'),
            'create' => CreatePrimaNotaConfig::route('/create'),
            'edit' => EditPrimaNotaConfig::route('/{record}/edit'),
        ];
    }
}
