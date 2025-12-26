<?php

namespace App\Filament\Resources\Praticas;

// use App\Filament\Resources\Praticas\Pages\CreatePratica;
use App\Filament\Resources\Praticas\Pages\EditPratica;
use App\Filament\Resources\Praticas\Pages\ListPraticas;
use App\Filament\Resources\Praticas\Pages\ViewPratica;
use App\Filament\Resources\Praticas\Schemas\PraticaForm;
use App\Filament\Resources\Praticas\Tables\PraticasTable;
use App\Models\Pratica;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class PraticaResource extends Resource
{
    protected static ?string $model = Pratica::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Pratica';

    protected static ?string $modelLabel = 'Pratica';

    protected static ?string $pluralModelLabel = 'Pratica';

    protected static UnitEnum|string|null $navigationGroup = 'Archivi';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'pratica';

    public static function form(Schema $schema): Schema
    {
        return PraticaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PraticasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProvvigioniRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPraticas::route('/'),
            //   'create' => CreatePratica::route('/create'),
            'view' => ViewPratica::route('/{record}'),
        ];
    }
}
