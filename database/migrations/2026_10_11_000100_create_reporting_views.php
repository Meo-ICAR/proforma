<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Viste di reporting, ENASARCO e COGE di Proforma (una per file in database/views, in ordine di dipendenza).
 * Rispetto al database precedente le viste agganciano la pratica con `pratiches.codice_pratica` (non più con `pratiches.id`,
 * ora intero) e leggono tipo prodotto e stato dalle tabelle `tipoprodotto` e `pratica_stati` del pacchetto unico-core.
 * Quelle che dipendono da tabelle o colonne che non esistono più (`calls`, `leads`, `tmpprovvigioni`, `provvigioni_coge`,
 * `invoices.fornitori_id`) si saltano.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->views() as $view) {
            try {
                DB::unprepared(file_get_contents(database_path("views/{$view}.sql")));
            } catch (QueryException $e) {
                // 1146 = tabella o vista inesistente, 1054 = colonna inesistente: la vista era già obsoleta nel database
                // precedente (MySQL non la rivalida finché non viene ricreata). Le viste usate dal codice sono verificate dai test.
                if (! in_array($e->errorInfo[1] ?? null, [1146, 1054], true)) {
                    throw $e;
                }
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->views()) as $view) {
            DB::unprepared("DROP VIEW IF EXISTS `{$view}`");
        }
    }

    /** @return list<string> */
    private function views(): array
    {
        return array_values(array_filter(array_map('trim', file(database_path('views/_ordine.txt')))));
    }
};
