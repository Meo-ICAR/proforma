<?php

namespace App\Filament\Resources\InvoiceIns;

use App\Filament\Resources\InvoiceIns\Pages\CreateInvoiceIn;
use App\Filament\Resources\InvoiceIns\Pages\EditInvoiceIn;
use App\Filament\Resources\InvoiceIns\Pages\ListInvoiceIns;
use App\Filament\Resources\InvoiceIns\Schemas\InvoiceInForm;
use App\Filament\Resources\InvoiceIns\Tables\InvoiceInsTable;
use App\Models\InvoiceIn;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InvoiceInResource extends Resource
{
    protected static ?string $model = InvoiceIn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Fatture';
    protected static ?string $modelLabel = 'Fatture';
    protected static ?string $pluralModelLabel = 'Fatture';
    protected static UnitEnum|string|null $navigationGroup = 'Archivi';
    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'fatture';

    public static function form(Schema $schema): Schema
    {
        return InvoiceInForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoiceInsTable::configure($table);
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
            'index' => ListInvoiceIns::route('/'),
            'create' => CreateInvoiceIn::route('/create'),
            'edit' => EditInvoiceIn::route('/{record}/edit'),
        ];
    }
}
