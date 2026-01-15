<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';  // Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Fatture';

    protected static ?string $modelLabel = 'Riconciliazione Fatture passive';

    protected static ?string $pluralModelLabel = 'Riconciliazione Fatture passive';

    //    protected static UnitEnum|string|null $navigationGroup = 'Archivi';
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'fornitore';

    /**
     * Get the navigation badge for the resource.
     */
    /*
     * public static function getNavigationBadge(): ?string
     * {
     *     return static::getModel()::where('isreconiled', false)->count() ?: null;
     * }
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProformasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
            // Edit page is intentionally disabled
            // 'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }

    /*
     * public static function canEdit(Model $record): bool
     * {
     *     return false;
     * }
     */
}
