<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Unicocoge non usa viste: ENASARCO, COGE e storico proforma leggono le query di App\Services\ReportingQueries. Si tolgono tutte le viste di reporting rimaste.
 */
return new class extends Migration
{
    private const VIEWS = [
        'v_calcolo_enasarco_annuo',
        'vwclientinew',
        'vwclientinewdetail',
        'vwcoordinamentobase',
        'vwcoordinati',
        'vwcoordinatori',
        'vwfatturato',
        'vwfatturepercompetenza',
        'vwfornitorinew',
        'vwfornitorinewdetail',
        'vwleadcalls',
        'vwleadcalltots',
        'vwpraticacoordinata',
        'vwprovv2cogedetail',
        'vwprovv2cogedetailx',
        'vwprovv2cogestorno',
        'vwprovvanomale',
        'vwprovvcogestorno',
        'vwprovvcoordinamento',
        'vwprovvdoppie',
        'vwprovvigioni',
        'vwprovvigioni_fatturato',
        'vwstipulated_at',
    ];

    public function up(): void
    {
        foreach (self::VIEWS as $view) {
            DB::unprepared("DROP VIEW IF EXISTS `{$view}`");
        }
    }

    public function down(): void
    {
        // Le viste non servono più al codice e non vengono ricreate.
    }
};
