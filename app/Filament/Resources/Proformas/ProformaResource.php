<?php

namespace App\Filament\Resources\Proformas;

use App\Filament\Resources\Proformas\Pages\CreateProforma;
use App\Filament\Resources\Proformas\Pages\EditProforma;
use App\Filament\Resources\Proformas\Pages\ListProformas;
use App\Filament\Resources\Proformas\RelationManagers\ProvvigioniRelationManager;
use App\Filament\Resources\Proformas\Schemas\ProformaForm;
use App\Filament\Resources\Proformas\Tables\ProformasTable;
use App\Filament\Traits\HasPlanAccess;
use App\Models\Proforma;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProformaResource extends Resource
{
    use HasPlanAccess;

    protected static ?string $model = Proforma::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static ?string $navigationLabel = 'Proforma';

    protected static ?string $modelLabel = 'Proforma';

    protected static ?string $pluralModelLabel = 'Proforma';

    // protected static UnitEnum|string|null $navigationGroup = 'Pratiche';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'emailsubject';

    public static function form(Schema $schema): Schema
    {
        return ProformaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProformasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProvvigioniRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProformas::route('/'),
            //  'create' => CreateProforma::route('/create'),
            'edit' => EditProforma::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getFormModelQuery(string $model): Builder
    {
        return parent::getFormModelQuery($model)
            ->with('fornitore');
    }
}
