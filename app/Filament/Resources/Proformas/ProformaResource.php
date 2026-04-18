<?php

namespace App\Filament\Resources\Proformas;

use App\Filament\Resources\Proformas\Pages\CreateProforma;
use App\Filament\Resources\Proformas\Pages\EditProforma;
use App\Filament\Resources\Proformas\Pages\ListProformas;
use App\Filament\Resources\Proformas\RelationManagers\ProvvigioniRelationManager;
use App\Filament\Resources\Proformas\Schemas\ProformaForm;
use App\Filament\Resources\Proformas\Tables\ProformasTable;
use App\Models\Proforma;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

/*
 * create view vwproformaistituto as
 * select 'Pagato' AS `stato`,`c`.`id` AS `fornitori_id`,`p`.`data_fattura` AS `sended`,sum(`p`.`importo`) AS `compenso`,'Storico MediaFacile' AS `compenso_descrizione` from (`provvigioni` `p` join `clientis` `c` on((`c`.`name` = `p`.`denominazione_riferimento`))) where ((`p`.`data_fattura` is not null) and (`p`.`tipo` = 'Istituto')) group by `p`.`stato`,`c`.`id`,`p`.`data_fattura`
 *
 * insert into proformas ( stato, fornitori_id, sended_at, compenso, compenso_descrizione ) select * from vwproformaistituto
 *
 * update provvigioni p
 * inner join clientis c on c.name =p.denominazione_riferimento
 * inner join proformas f on f.fornitori_id = c.id
 * set p.proforma_id = f.id
 * where p.tipo = 'Istituto'
 * and p.data_fattura is not null
 * and p.data_fattura = f.sended_at
 *
 * UPDATE proformas p  INNER JOIN clientis c on c.id =p.fornitori_id
 * set  emailsubject = concat('Storico #',p.id,' ',c.name,' Totale ',p.compenso)  , emailto = c.email
 * where stato = 'Pagato' and emailsubject is null
 *
 * create view vwproformaagente as
 * select 'Pagato' AS `stato`,`c`.`id` AS `fornitori_id`,`p`.`data_fattura` AS `sended`,sum(`p`.`importo`) AS `compenso`,'Storico MediaFacile' AS `compenso_descrizione` from (`provvigioni` `p` join `fornitoris` `c` on((`c`.`name` = `p`.`denominazione_riferimento`))) where ((`p`.`data_fattura` is not null) and (`p`.`tipo` = 'Agente') and (p.proforma_id is null)) group by `p`.`stato`,`c`.`id`,`p`.`data_fattura`
 *
 *  * insert into proformas ( stato, fornitori_id, sended_at, compenso, compenso_descrizione ) select * from vwproformaagente
 *
 * update provvigioni p
 * inner join fornitoris  c on c.name =p.denominazione_riferimento
 *  inner join proformas f on f.fornitori_id = c.id
 *  set p.proforma_id = f.id
 *  where p.tipo = 'Agente'
 *  and p.data_fattura is not null
 *  and p.data_fattura = f.sended_at
 *  and p.proforma_id is null
 *
 * UPDATE proformas p  INNER JOIN fornitoris c on c.id =p.fornitori_id
 * set  emailsubject = concat('Storico #',p.id,' ',c.name,' Totale ',p.compenso)  , emailto = c.email
 * where stato = 'Pagato' and emailsubject is null
 */

class ProformaResource extends Resource
{
    protected static ?string $model = Proforma::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-paper-airplane';  // Heroicon::OutlinedRectangleStack;

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

    public static function getFormModelQuery(string $model): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getFormModelQuery($model)
            ->with('fornitore');
    }
}
