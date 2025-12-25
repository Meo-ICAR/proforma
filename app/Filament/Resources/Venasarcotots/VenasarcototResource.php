<?php

namespace App\Filament\Resources\Venasarcotots;

use App\Filament\Resources\Venasarcotots\Pages\CreateVenasarcotot;
use App\Filament\Resources\Venasarcotots\Pages\EditVenasarcotot;
use App\Filament\Resources\Venasarcotots\Pages\ListVenasarcotots;
use App\Filament\Resources\Venasarcotots\Pages\ViewVenasarcotot;
use App\Filament\Resources\Venasarcotots\Schemas\VenasarcototForm;
use App\Filament\Resources\Venasarcotots\Schemas\VenasarcototInfolist;
use App\Filament\Resources\Venasarcotots\Tables\VenasarcototsTable;
use App\Models\Venasarcotot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VenasarcototResource extends Resource
{
    protected static ?string $model = Venasarcotot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Enasarco Massimali';

    public static function form(Schema $schema): Schema
    {
        return VenasarcototForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VenasarcototInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenasarcototsTable::configure($table);
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
            'index' => ListVenasarcotots::route('/'),
//'create' => CreateVenasarcotot::route('/create'),
         //   'view' => ViewVenasarcotot::route('/{record}'),
          //  'edit' => EditVenasarcotot::route('/{record}/edit'),
        ];
    }
}
