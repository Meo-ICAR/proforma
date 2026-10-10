<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Baseline delle viste di reporting/ENASARCO/COGE lette dai model e dai comandi
 * Artisan (vwenasarco*, vwcoge*, vwproforma*, vwprovvdoppie, vwstipulated_at,
 * vwbancastipulated). Devono essere create dopo tutte le tabelle base referenziate.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Baseline legacy MySQL-only: sul suite di test (sqlite in-memory) queste
        // CREATE TABLE/VIEW letterali non sono compatibili, quindi vengono saltate.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP VIEW IF EXISTS `vwbancastipulated`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwbancastipulated` AS select `pratiches`.`denominazione_banca` AS `banca`,min(`pratiches`.`sended_at`) AS `stipulated_at` from `pratiches` group by `pratiches`.`denominazione_banca`;');

        DB::unprepared('DROP VIEW IF EXISTS `vwcoge`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwcoge` AS select date_format(`p`.`erogated_at`,\'%Y-%m\') AS `mese`,count(0) AS `n`,sum((if((`p`.`entrata_uscita` = \'Entrata\'),1,0) * `p`.`importo`)) AS `entrata`,sum((if((`p`.`entrata_uscita` = \'Entrata\'),0,1) * `p`.`importo`)) AS `uscita`,0 AS `storno_entrata`,0 AS `storno_uscita` from `provvigioni` `p` where (`p`.`erogated_at` is not null) group by date_format(`p`.`erogated_at`,\'%Y-%m\') order by date_format(`p`.`erogated_at`,\'%Y-%m\') desc;');

        DB::unprepared('DROP VIEW IF EXISTS `vwcogestorno`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwcogestorno` AS select date_format(`p`.`data_status`,\'%Y-%m\') AS `mese`,count(0) AS `n`,sum((if((`p`.`entrata_uscita` = \'Entrata\'),1,0) * `p`.`importo`)) AS `storno_entrata`,sum((if((`p`.`entrata_uscita` = \'Entrata\'),0,1) * `p`.`importo`)) AS `storno_uscita` from `provvigioni` `p` where (`p`.`status_compenso` = \'Pratica stornata\') group by date_format(`p`.`data_status`,\'%Y-%m\') order by date_format(`p`.`data_status`,\'%Y-%m\') desc;');

        DB::unprepared('DROP VIEW IF EXISTS `vwenasarco`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwenasarco` AS select year(`p`.`data_fattura`) AS `competenza`,`p`.`denominazione_riferimento` AS `produttore`,`f`.`enasarco` AS `enasarco`,sum(`p`.`importo`) AS `montante`,((sum(`p`.`importo`) * (`e`.`aliquota_agente` + `e`.`aliquota_soc`)) / 100) AS `contributo` from ((`provvigioni` `p` join `fornitoris` `f` on((`f`.`piva` = `p`.`piva`))) join `enasarcos` `e` on(((`e`.`competenza` = year(`p`.`data_fattura`)) and (`e`.`enasarco` = `f`.`enasarco`)))) where ((`p`.`stato` is not null) and (`p`.`data_fattura` is not null) and (`p`.`stato` <> \'Annullato\') and (`p`.`entrata_uscita` = \'Uscita\')) group by year(`p`.`data_fattura`),`p`.`denominazione_riferimento`,`f`.`enasarco`,`e`.`aliquota_agente`,`e`.`aliquota_soc` order by year(`p`.`data_fattura`) desc,`p`.`denominazione_riferimento`;');

        DB::unprepared('DROP VIEW IF EXISTS `vwenasarcoprovv`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwenasarcoprovv` AS select year(`p`.`data_fattura`) AS `competenza`,quarter(`p`.`data_fattura`) AS `Trimestre`,`p`.`denominazione_riferimento` AS `produttore`,`f`.`enasarco` AS `enasarco`,`p`.`importo` AS `montante`,((`p`.`importo` * (`e`.`aliquota_agente` + `e`.`aliquota_soc`)) / 100) AS `contributo` from ((`provvigioni` `p` join `fornitoris` `f` on((`f`.`piva` = `p`.`piva`))) join `enasarcos` `e` on(((`e`.`competenza` = year(`p`.`data_fattura`)) and (`e`.`enasarco` = `f`.`enasarco`)))) where ((`p`.`stato` is not null) and (`p`.`data_fattura` is not null) and (`p`.`stato` <> \'Annullato\') and (`p`.`entrata_uscita` = \'Uscita\'));');

        DB::unprepared('DROP VIEW IF EXISTS `vwenasarcotot`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwenasarcotot` AS select `v`.`produttore` AS `produttore`,`v`.`montante` AS `montante`,`v`.`contributo` AS `contributo`,concat(if((least(`e`.`massimo`,`v`.`contributo`) = `e`.`massimo`),\'+\',\'\'),if((greatest(`e`.`minimale`,`v`.`contributo`) = `e`.`minimale`),\'-\',\'\')) AS `X`,least(`e`.`massimo`,greatest(`e`.`minimale`,`v`.`contributo`)) AS `imposta`,`v`.`montante` AS `firr`,`v`.`competenza` AS `competenza`,`e`.`enasarco` AS `enasarco` from (`vwenasarco` `v` left join `enasarcos` `e` on(((`e`.`competenza` = `v`.`competenza`) and (`e`.`enasarco` = `v`.`enasarco`)))) order by `v`.`competenza` desc,`v`.`produttore`;');

        DB::unprepared('DROP VIEW IF EXISTS `vwenasarcotrimestre`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwenasarcotrimestre` AS select year(`p`.`data_fattura`) AS `competenza`,quarter(`p`.`data_fattura`) AS `Trimestre`,`p`.`denominazione_riferimento` AS `produttore`,`f`.`enasarco` AS `enasarco`,sum(`p`.`importo`) AS `montante`,((sum(`p`.`importo`) * (`e`.`aliquota_agente` + `e`.`aliquota_soc`)) / 100) AS `contributo` from ((`provvigioni` `p` join `fornitoris` `f` on((`f`.`piva` = `p`.`piva`))) join `enasarcos` `e` on(((`e`.`competenza` = year(`p`.`data_fattura`)) and (`e`.`enasarco` = `f`.`enasarco`)))) where ((`p`.`stato` is not null) and (`p`.`data_fattura` is not null) and (`p`.`stato` <> \'Annullato\') and (`p`.`entrata_uscita` = \'Uscita\')) group by year(`p`.`data_fattura`),quarter(`p`.`data_fattura`),`p`.`denominazione_riferimento`,`f`.`enasarco`,`e`.`aliquota_agente`,`e`.`aliquota_soc` order by year(`p`.`data_fattura`) desc,`p`.`denominazione_riferimento`;');

        DB::unprepared('DROP VIEW IF EXISTS `vwproformaagente`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwproformaagente` AS select \'Pagato\' AS `stato`,`c`.`id` AS `fornitori_id`,`p`.`data_fattura` AS `sended`,sum(`p`.`importo`) AS `compenso`,\'Storico MediaFacile\' AS `compenso_descrizione` from (`provvigioni` `p` join `fornitoris` `c` on((`c`.`name` = `p`.`denominazione_riferimento`))) where ((`p`.`data_fattura` is not null) and (`p`.`tipo` = \'Agente\') and (`p`.`proforma_id` is null)) group by `p`.`stato`,`c`.`id`,`p`.`data_fattura`;');

        DB::unprepared('DROP VIEW IF EXISTS `vwproformaistituto`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwproformaistituto` AS select \'Pagato\' AS `stato`,`c`.`id` AS `fornitori_id`,`p`.`data_fattura` AS `sended`,sum(`p`.`importo`) AS `compenso`,\'Storico MediaFacile\' AS `compenso_descrizione` from (`provvigioni` `p` join `clientis` `c` on((`c`.`name` = `p`.`denominazione_riferimento`))) where ((`p`.`data_fattura` is not null) and (`p`.`tipo` = \'Istituto\')) group by `p`.`stato`,`c`.`id`,`p`.`data_fattura`;');

        DB::unprepared('DROP VIEW IF EXISTS `vwprovvdoppie`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwprovvdoppie` AS select concat(`p`.`cognome_cliente`,\' \',`p`.`nome_cliente`) AS `Cliente`,`p1`.`id_pratica` AS `id_pratica`,`p`.`tipo_prodotto` AS `tipo_prodotto`,`p1`.`denominazione_riferimento` AS `denominazione_riferimento`,`p1`.`id` AS `n_provvigione`,`p1`.`importo` AS `importo`,`p1`.`data_fattura` AS `data_fattura`,`p2`.`id` AS `n_doppione`,`p2`.`importo` AS `doppione_importo`,`p1`.`status_compenso` AS `status_compenso`,`p1`.`data_status` AS `data_status`,`p1`.`descrizione` AS `descrizione`,`p2`.`data_status` AS `doppione_del`,`p2`.`status_compenso` AS `doppione_status`,`p2`.`data_fattura` AS `fattura_doppione` from ((`provvigioni` `p1` join `pratiches` `p` on((`p`.`id` = `p1`.`id_pratica`))) join `provvigioni` `p2` on(((`p1`.`id_pratica` = `p2`.`id_pratica`) and (`p1`.`tipo` = `p2`.`tipo`) and (`p1`.`denominazione_riferimento` = `p2`.`denominazione_riferimento`) and (`p1`.`descrizione` = `p2`.`descrizione`)))) where ((`p1`.`id` < `p2`.`id`) and (`p1`.`data_fattura` is not null)) order by `p1`.`data_status` desc;');

        DB::unprepared('DROP VIEW IF EXISTS `vwstipulated_at`');
        DB::unprepared('CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vwstipulated_at` AS select `pratiches`.`partita_iva_agente` AS `partita_iva_agente`,min(`pratiches`.`sended_at`) AS `stipulated_at` from `pratiches` group by `pratiches`.`partita_iva_agente`;');
    }

    public function down(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS `vwstipulated_at`');
        DB::unprepared('DROP VIEW IF EXISTS `vwprovvdoppie`');
        DB::unprepared('DROP VIEW IF EXISTS `vwproformaistituto`');
        DB::unprepared('DROP VIEW IF EXISTS `vwproformaagente`');
        DB::unprepared('DROP VIEW IF EXISTS `vwenasarcotrimestre`');
        DB::unprepared('DROP VIEW IF EXISTS `vwenasarcotot`');
        DB::unprepared('DROP VIEW IF EXISTS `vwenasarcoprovv`');
        DB::unprepared('DROP VIEW IF EXISTS `vwenasarco`');
        DB::unprepared('DROP VIEW IF EXISTS `vwcogestorno`');
        DB::unprepared('DROP VIEW IF EXISTS `vwcoge`');
        DB::unprepared('DROP VIEW IF EXISTS `vwbancastipulated`');
    }
};
