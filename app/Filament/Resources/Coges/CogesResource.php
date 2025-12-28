<?php

namespace App\Filament\Resources\Coges;

use App\Filament\Resources\Coges\Pages\CreateCoges;
use App\Filament\Resources\Coges\Pages\EditCoges;
use App\Filament\Resources\Coges\Pages\ListCoges;
use App\Filament\Resources\Coges\Pages\ViewCoges;
use App\Filament\Resources\Coges\Schemas\CogesForm;
use App\Filament\Resources\Coges\Schemas\CogesInfolist;
use App\Filament\Resources\Coges\Tables\CogesTable;
use App\Models\Coges;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class CogesResource extends Resource
{
    protected static ?string $model = Coges::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';  // Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Contabilita';

    protected static ?string $modelLabel = 'Contabilita';

    protected static ?string $pluralModelLabel = 'Contabilita';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'coges';

    public static function form(Schema $schema): Schema
    {
        return CogesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CogesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CogesTable::configure($table);
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
            'index' => ListCoges::route('/'),
            'create' => CreateCoges::route('/create'),
            'view' => ViewCoges::route('/{record}'),
            'edit' => EditCoges::route('/{record}/edit'),
        ];
    }
}
