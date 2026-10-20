<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `id_pratica` è il codice della pratica che arriva dall'API di Mediafacile (testo, es. «QT00029»); il riferimento numerico alle
     * pratiche di questo database è `pratica_id`, che resta anche quando la procedura parallela sarà dismessa. Le righe che avevano
     * ancora l'id numerico in `id_pratica` lo spostano in `pratica_id` e prendono il codice dalla pratica.
     */
    public function up(): void
    {
        if (! Schema::hasTable('provvigioni')) {
            return;
        }

        $tipo = DB::scalar("select data_type from information_schema.columns where table_schema = database() and table_name = 'provvigioni' and column_name = 'id_pratica'");

        if ($tipo === null || $tipo === 'varchar') {
            return;
        }

        if (! Schema::hasColumn('provvigioni', 'pratica_id')) {
            DB::statement("ALTER TABLE provvigioni ADD COLUMN pratica_id bigint unsigned NULL COMMENT 'pratiches.id' AFTER id_pratica, ADD INDEX provvigioni_pratica_id_index (pratica_id)");
        }

        DB::statement('UPDATE provvigioni SET pratica_id = id_pratica WHERE pratica_id IS NULL');

        $chiave = DB::scalar("select constraint_name from information_schema.key_column_usage where table_schema = database() and table_name = 'provvigioni' and column_name = 'id_pratica' and referenced_table_name = 'pratiches'");

        if ($chiave !== null) {
            DB::statement("ALTER TABLE provvigioni DROP FOREIGN KEY `{$chiave}`");
        }

        DB::statement("ALTER TABLE provvigioni MODIFY id_pratica varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Codice della pratica (ID Pratica di Mediafacile)'");
        DB::statement('UPDATE provvigioni p INNER JOIN pratiches pr ON pr.id = p.pratica_id SET p.id_pratica = pr.codice_pratica');
    }

    /**
     * Non si torna a un id numerico: i codici delle pratiche andrebbero persi.
     */
    public function down(): void {}
};
