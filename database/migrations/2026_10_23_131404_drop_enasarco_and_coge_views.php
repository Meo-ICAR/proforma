<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ENASARCO, COGE e storico proforma non leggono più le viste: le query sono in App\Services\ReportingQueries.
 */
return new class extends Migration
{
    private const VIEWS = [
        'vwenasarcotot',
        'vwenasarcotrimestre',
        'vwenasarcoprovv',
        'vwenasarco',
        'vwcogestorno',
        'vwcoge',
        'vwproformaagente',
        'vwproformaistituto',
        'vwbancastipulated',
        'vwcogeprovvigioni',
        'vwenasarcoanno',
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
