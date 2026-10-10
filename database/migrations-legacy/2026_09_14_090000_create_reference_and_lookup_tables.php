<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline della tabella legacy "address_types", "client_types", "coges", "compensos", "enasarcos", "firrs", "fornitoriroles", "onorabilita", "pratiches_statos", "provvigioni_statos", "tipoprodotto", "tmpfornitoriemail", "vcoge", "venasarcotot", "venasarcotrimestre"
 * (schema esistente a DB, mai coperto da migration Laravel: vedi
 * resources/manuals/manuale-tecnico.html §3/§15). Le CREATE TABLE sono
 * riprodotte fedelmente da un dump dello schema live, per poter ricostruire
 * l'ambiente da zero (nuovi sviluppatori, CI). Su un DB dove queste tabelle
 * esistono già, la migration è registrata come già eseguita e non va
 * rilanciata (vedi nota nel changelog / commit che l'ha introdotta).
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

        DB::unprepared('CREATE TABLE `address_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco tipo indirizzo\',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `client_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco tipo cliente\',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Descrizione\',
  `is_person` tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'Persona fisica (true) o giuridica (false)\',
  `is_company` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Indica se è una società/azienda\',
  `privacy_role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Ruolo Privacy (es. Titolare Autonomo, Responsabile Esterno)\',
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Finalità del trattamento\',
  `data_subjects` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Categorie di Interessati\',
  `data_categories` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Categorie di Dati Trattati\',
  `retention_period` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Tempi di Conservazione (Data Retention)\',
  `extra_eu_transfer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Trasferimento Extra-UE\',
  `security_measures` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Misure di Sicurezza\',
  `privacy_data` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Altri Dati Privacy\',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'Data di creazione\',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'Ultima modifica\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `coges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `fonte` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entrata_uscita` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `conto_avere` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione_avere` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `conto_dare` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descrizione_dare` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `annotazioni` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Piano dei conti e configurazioni per la contabilità generale\';');

        DB::unprepared('CREATE TABLE `compensos` (
  `status_compenso` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isperfezionato` int NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`status_compenso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `enasarcos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco\',
  `competenza` year DEFAULT \'2025\' COMMENT \'Anno di competenza\',
  `enasarco` enum(\'monomandatario\',\'plurimandatario\',\'societa\',\'no\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'plurimandatario\' COMMENT \'Tipo di mandato ENASARCO\',
  `minimo` decimal(10,2) DEFAULT NULL COMMENT \'Minimo imponibile\',
  `massimo` decimal(10,2) DEFAULT NULL COMMENT \'Massimo imponibile\',
  `minimale` decimal(10,2) DEFAULT NULL COMMENT \'Contributo minimo annuo dovuto per il tipo di mandato\',
  `massimale` decimal(10,2) DEFAULT NULL COMMENT \'Soglia massima di contribuzione oltre la quale non si versano contributi\',
  `aliquota_soc` decimal(5,2) DEFAULT NULL COMMENT \'Aliquota a carico società\',
  `aliquota_agente` decimal(5,2) DEFAULT NULL COMMENT \'Aliquota a carico agente\',
  `created_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di creazione\',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di ultimo aggiornamento\',
  PRIMARY KEY (`id`),
  KEY `enasarco_enasarco_competenza_index` (`enasarco`,`competenza`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Parametri, aliquote e soglie ENASARCO per anno di competenza\';');

        DB::unprepared('CREATE TABLE `firrs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `minimo` decimal(10,2) DEFAULT NULL,
  `massimo` decimal(10,2) DEFAULT NULL,
  `aliquota` decimal(5,2) DEFAULT NULL,
  `competenza` int DEFAULT \'2025\',
  `enasarco` enum(\'monomandatario\',\'plurimandatario\',\'societa\',\'no\') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT \'plurimandatario\',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT=\'Aliquote per il calcolo dell\'\'Indennità Risoluzione Rapporto (FIRR)\';');

        DB::unprepared('CREATE TABLE `fornitoriroles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Nome del ruolo (es. Agente Senior, Segnalatore)\',
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice univoco del ruolo (es. AG_SENIOR, SEGNALATORE)\',
  `level` int DEFAULT \'1\' COMMENT \'Livello gerarchico (es. 1 = base, 5 = manager). Utile per calcoli provvigionali a cascata.\',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Breve descrizione dei permessi o del ruolo nella rete\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Tabella dei ruoli e livelli della rete dei mediatori creditizi.\';');

        DB::unprepared('CREATE TABLE `onorabilita` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nominativo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_ultimo_carico_pendente` date DEFAULT NULL,
  `data_ultima_onorabilita` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `pratiches_statos` (
  `stato_pratica` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Stato attuale della pratica\',
  `isrejected` int NOT NULL DEFAULT \'0\' COMMENT \'Flag: 1 se stato rifiutato/annullato\',
  `isworking` int NOT NULL DEFAULT \'0\' COMMENT \'Flag: 1 se stato in lavorazione\',
  `isestingued` int NOT NULL DEFAULT \'0\' COMMENT \'Flag: 1 se stato estinto/concluso\',
  PRIMARY KEY (`stato_pratica`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT=\'Stati possibili delle pratiche e loro flag\';');

        DB::unprepared('CREATE TABLE `provvigioni_statos` (
  `stato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Inserito\' COMMENT \'Stato della provvigione\',
  PRIMARY KEY (`stato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `tipoprodotto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Tipologia di prodotto finanziario\',
  `tipo_prodotto` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_external` tinyint(1) DEFAULT \'0\',
  `is_active` tinyint(1) DEFAULT NULL,
  `is_oneclient` tinyint(1) DEFAULT \'1\',
  `oam` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_provvigioni` enum(\'Lordo\',\'Erogato\',\'Netto\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `uq_tipoprodotto_tipo_prodotto` (`tipo_prodotto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `tmpfornitoriemail` (
  `piva` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Partita IVA\',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `vcoge` (
  `id` tinyint NOT NULL AUTO_INCREMENT,
  `mese` varchar(7) DEFAULT NULL,
  `entrata` decimal(38,2) DEFAULT NULL,
  `uscita` decimal(38,2) DEFAULT NULL,
  `storno_entrata` decimal(10,2) NOT NULL DEFAULT \'0.00\',
  `storno_uscita` decimal(10,2) NOT NULL DEFAULT \'0.00\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `venasarcotot` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produttore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Ragione sociale del referente\',
  `montante` decimal(37,2) DEFAULT NULL,
  `contributo` decimal(47,8) DEFAULT NULL,
  `X` varchar(2) DEFAULT NULL,
  `imposta` decimal(47,8) DEFAULT NULL,
  `firr` decimal(37,2) DEFAULT NULL,
  `competenza` int DEFAULT NULL,
  `enasarco` enum(\'monomandatario\',\'plurimandatario\',\'societa\',\'no\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'plurimandatario\' COMMENT \'Tipo di mandato ENASARCO\',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `venasarcotrimestre` (
  `id` int NOT NULL AUTO_INCREMENT,
  `competenza` int unsigned DEFAULT NULL,
  `Trimestre` int DEFAULT NULL,
  `produttore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Ragione sociale del referente\',
  `enasarco` enum(\'no\',\'monomandatario\',\'plurimandatario\',\'societa\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'plurimandatario\' COMMENT \'Tipo di mandato ENASARCO\',
  `montante` decimal(37,2) DEFAULT NULL,
  `contributo` decimal(47,8) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');
    }

    public function down(): void
    {
        Schema::dropIfExists('venasarcotrimestre');
        Schema::dropIfExists('venasarcotot');
        Schema::dropIfExists('vcoge');
        Schema::dropIfExists('tmpfornitoriemail');
        Schema::dropIfExists('tipoprodotto');
        Schema::dropIfExists('provvigioni_statos');
        Schema::dropIfExists('pratiches_statos');
        Schema::dropIfExists('onorabilita');
        Schema::dropIfExists('fornitoriroles');
        Schema::dropIfExists('firrs');
        Schema::dropIfExists('enasarcos');
        Schema::dropIfExists('compensos');
        Schema::dropIfExists('coges');
        Schema::dropIfExists('client_types');
        Schema::dropIfExists('address_types');
    }
};
