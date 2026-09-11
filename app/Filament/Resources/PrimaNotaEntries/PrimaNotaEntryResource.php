<?php

namespace App\Filament\Resources\PrimaNotaEntries;

use App\Filament\Resources\PrimaNotaEntries\Pages\CreatePrimaNotaEntry;
use App\Filament\Resources\PrimaNotaEntries\Pages\EditPrimaNotaEntry;
use App\Filament\Resources\PrimaNotaEntries\Pages\ListPrimaNotaEntries;
use App\Filament\Resources\PrimaNotaEntries\Schemas\PrimaNotaEntryForm;
use App\Filament\Resources\PrimaNotaEntries\Tables\PrimaNotaEntriesTable;
use App\Models\PrimaNotaEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrimaNotaEntryResource extends Resource
{
    protected static ?string $model = PrimaNotaEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PrimaNotaEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrimaNotaEntriesTable::configure($table);
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
            'index' => ListPrimaNotaEntries::route('/'),
            'create' => CreatePrimaNotaEntry::route('/create'),
            'edit' => EditPrimaNotaEntry::route('/{record}/edit'),
        ];
    }
}
