<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Query di reporting ENASARCO, COGE e storico proforma (prima erano le viste `vw*`).
 * Le tabelle del pacchetto unico-core (`fornitoris`, `clientis`) si leggono sul database della connessione `core`.
 */
class ReportingQueries
{
    /**
     * Provvigioni per mese di erogazione (colonne: mese, n, entrata, uscita).
     */
    public static function coge(): Builder
    {
        return DB::table('provvigioni as p')
            ->whereNotNull('p.erogated_at')
            ->groupByRaw("date_format(p.erogated_at, '%Y-%m')")
            ->orderByRaw("date_format(p.erogated_at, '%Y-%m') desc")
            ->selectRaw("date_format(p.erogated_at, '%Y-%m') as mese")
            ->selectRaw('count(0) as n')
            ->selectRaw("sum(if(p.entrata_uscita = 'Entrata', 1, 0) * p.importo) as entrata")
            ->selectRaw("sum(if(p.entrata_uscita = 'Entrata', 0, 1) * p.importo) as uscita");
    }

    /**
     * Storni per mese dello stato (colonne: mese, n, storno_entrata, storno_uscita).
     */
    public static function cogeStorno(): Builder
    {
        return DB::table('provvigioni as p')
            ->where('p.status_compenso', 'Pratica stornata')
            ->groupByRaw("date_format(p.data_status, '%Y-%m')")
            ->orderByRaw("date_format(p.data_status, '%Y-%m') desc")
            ->selectRaw("date_format(p.data_status, '%Y-%m') as mese")
            ->selectRaw('count(0) as n')
            ->selectRaw("sum(if(p.entrata_uscita = 'Entrata', 1, 0) * p.importo) as storno_entrata")
            ->selectRaw("sum(if(p.entrata_uscita = 'Entrata', 0, 1) * p.importo) as storno_uscita");
    }

    /**
     * Contributo ENASARCO per trimestre (colonne: produttore, montante, competenza, Trimestre, enasarco, contributo).
     */
    public static function enasarcoTrimestre(): Builder
    {
        return self::enasarcoBase()
            ->groupByRaw('year(p.data_fattura), quarter(p.data_fattura), p.denominazione_riferimento, f.enasarco, e.aliquota_agente, e.aliquota_soc')
            ->orderByRaw('year(p.data_fattura) desc, p.denominazione_riferimento')
            ->selectRaw('p.denominazione_riferimento as produttore')
            ->selectRaw('sum(p.importo) as montante')
            ->selectRaw('year(p.data_fattura) as competenza')
            ->selectRaw('quarter(p.data_fattura) as Trimestre')
            ->selectRaw('f.enasarco as enasarco')
            ->selectRaw('(sum(p.importo) * (e.aliquota_agente + e.aliquota_soc)) / 100 as contributo');
    }

    /**
     * Contributo ENASARCO per anno e produttore (colonne: competenza, produttore, enasarco, montante, contributo).
     */
    public static function enasarcoAnno(): Builder
    {
        return self::enasarcoBase()
            ->groupByRaw('year(p.data_fattura), p.denominazione_riferimento, f.enasarco, e.aliquota_agente, e.aliquota_soc')
            ->orderByRaw('year(p.data_fattura) desc, p.denominazione_riferimento')
            ->selectRaw('year(p.data_fattura) as competenza')
            ->selectRaw('p.denominazione_riferimento as produttore')
            ->selectRaw('f.enasarco as enasarco')
            ->selectRaw('sum(p.importo) as montante')
            ->selectRaw('(sum(p.importo) * (e.aliquota_agente + e.aliquota_soc)) / 100 as contributo');
    }

    /**
     * Totali ENASARCO con minimale e massimale (colonne: produttore, montante, contributo, X, imposta, firr, competenza, enasarco).
     */
    public static function enasarcoTotali(): Builder
    {
        return DB::query()
            ->fromSub(self::enasarcoAnno(), 'v')
            ->leftJoin('enasarcos as e', function (JoinClause $join) {
                $join->on('e.competenza', '=', 'v.competenza')->on('e.enasarco', '=', 'v.enasarco');
            })
            ->orderByRaw('v.competenza desc, v.produttore')
            ->select('v.produttore', 'v.montante', 'v.contributo')
            ->selectRaw("concat(if(least(e.massimo, v.contributo) = e.massimo, '+', ''), if(greatest(e.minimale, v.contributo) = e.minimale, '-', '')) as X")
            ->selectRaw('least(e.massimo, greatest(e.minimale, v.contributo)) as imposta')
            ->selectRaw('v.montante as firr')
            ->addSelect('v.competenza')
            ->selectRaw('e.enasarco as enasarco');
    }

    /**
     * Compensi degli agenti già fatturati e non ancora in un proforma (colonne: stato, fornitori_id, sended, compenso, compenso_descrizione).
     */
    public static function proformaAgente(): Builder
    {
        return DB::table('provvigioni as p')
            ->join(self::coreTable('fornitoris').' as c', 'c.name', '=', 'p.denominazione_riferimento')
            ->whereNotNull('p.data_fattura')
            ->where('p.tipo', 'Agente')
            ->whereNull('p.proforma_id')
            ->groupBy('p.stato', 'c.id', 'p.data_fattura')
            ->selectRaw("'Pagato' as stato")
            ->selectRaw('c.id as fornitori_id')
            ->selectRaw('p.data_fattura as sended')
            ->selectRaw('sum(p.importo) as compenso')
            ->selectRaw("'Storico MediaFacile' as compenso_descrizione");
    }

    /**
     * Compensi degli istituti già fatturati (colonne: stato, fornitori_id, sended, compenso, compenso_descrizione).
     */
    public static function proformaIstituto(): Builder
    {
        return DB::table('provvigioni as p')
            ->join(self::coreTable('clientis').' as c', 'c.name', '=', 'p.denominazione_riferimento')
            ->whereNotNull('p.data_fattura')
            ->where('p.tipo', 'Istituto')
            ->groupBy('p.stato', 'c.id', 'p.data_fattura')
            ->selectRaw("'Pagato' as stato")
            ->selectRaw('c.id as fornitori_id')
            ->selectRaw('p.data_fattura as sended')
            ->selectRaw('sum(p.importo) as compenso')
            ->selectRaw("'Storico MediaFacile' as compenso_descrizione");
    }

    /**
     * Inserisce in `proformas` i compensi storici di agenti o istituti che non hanno ancora un proforma alla stessa data.
     *
     * @param  'Agente'|'Istituto'  $tipo
     */
    public static function insertMissingProformas(string $tipo): int
    {
        $source = $tipo === 'Agente' ? self::proformaAgente() : self::proformaIstituto();

        return DB::table('proformas')->insertUsing(
            ['stato', 'fornitori_id', 'sended_at', 'compenso', 'compenso_descrizione'],
            DB::query()
                ->fromSub($source, 'v')
                ->leftJoin('proformas as p', function (JoinClause $join) {
                    $join->on('p.fornitori_id', '=', 'v.fornitori_id')->on('p.sended_at', '=', 'v.sended');
                })
                ->whereNull('p.id')
                ->select('v.stato', 'v.fornitori_id', 'v.sended', 'v.compenso', 'v.compenso_descrizione')
        );
    }

    /**
     * Provvigioni di uscita fatturate con l'aliquota ENASARCO dell'anno del fornitore.
     */
    private static function enasarcoBase(): Builder
    {
        return DB::table('provvigioni as p')
            ->join(self::coreTable('fornitoris').' as f', 'f.piva', '=', 'p.piva')
            ->join('enasarcos as e', function (JoinClause $join) {
                $join->whereRaw('e.competenza = year(p.data_fattura)')->on('e.enasarco', '=', 'f.enasarco');
            })
            ->whereNotNull('p.stato')
            ->whereNotNull('p.data_fattura')
            ->where('p.stato', '<>', 'Annullato')
            ->where('p.entrata_uscita', 'Uscita');
    }

    /**
     * Nome della tabella del pacchetto qualificato con il database della connessione `core`.
     */
    private static function coreTable(string $table): string
    {
        return DB::connection(config('unico-core.connection'))->getDatabaseName().'.'.$table;
    }
}
