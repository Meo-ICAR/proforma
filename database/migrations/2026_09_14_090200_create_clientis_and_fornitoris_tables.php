<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline della tabella legacy "clientis", "fornitoris", "blacklist_clienti_employees", "blacklist_clienti_fornitori", "addresses", "tipoprodotto_sub", "tipoprodotto_sub_constraints"
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

        DB::unprepared('CREATE TABLE `clientis` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'ID univoco del cliente/banca\',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `piva` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stipulated_at` date DEFAULT NULL COMMENT \'Data stipula contratto convenzione\',
  `abi` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Abi per banche o numero RUI ISVASS\',
  `abi_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome ufficiale banca\',
  `dismissed_at` date DEFAULT NULL COMMENT \'Data cessazione rapporto convenzione\',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Banca / Assicurazione / Utility\',
  `oam` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice di iscrizione OAM\',
  `oam_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Denominazione OAM\',
  `oam_at` date DEFAULT NULL COMMENT \'Data iscrizione OAM\',
  `numero_iscrizione_rui` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero iscrizione OAM\',
  `ivass` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice di iscrizione IVASS\',
  `ivass_at` date DEFAULT NULL COMMENT \'Data iscrizione IVASS\',
  `ivass_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Denominazione OAM\',
  `ivass_section` enum(\'A\',\'B\',\'C\',\'D\',\'E\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Sezione IVASS\',
  `mandate_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero di protocollo o identificativo del contratto di mandato\',
  `start_date` date DEFAULT NULL COMMENT \'Data di decorrenza del mandato\',
  `end_date` date DEFAULT NULL COMMENT \'Data di scadenza (NULL se a tempo indeterminato)\',
  `is_exclusive` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Indica se il mandato prevede l\'\'esclusiva per quella categoria\',
  `status` enum(\'ATTIVO\',\'SCADUTO\',\'RECEDUTO\',\'SOSPESO\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'ATTIVO\' COMMENT \'Stato operativo del mandato\',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note su provvigioni particolari o patti specifici\',
  `principal_type` enum(\'--\',\'banca\',\'broker\',\'captive\',\'assicurazione\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'banca\' COMMENT \'Tipologia del mandante\',
  `submission_type` enum(\'--\',\'accesso portale\',\'inoltro\',\'entrambi\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'accesso portale\' COMMENT \'Modalità inoltro pratiche\',
  `cf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice fiscale\',
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_reported` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Accordi di segnalazione\',
  `privacy_contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Email contatto privacy\',
  `dpo_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Email DPO\',
  `coge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice contabile COGE\',
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `citta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'5c044917-15b3-4471-90c9-38061fcca754\' COMMENT \'ID dell\'\'azienda di riferimento\',
  `customertype_id` bigint unsigned DEFAULT NULL COMMENT \'Riferimento al tipo di cliente (chiave esterna)\',
  `is_active` tinyint unsigned DEFAULT \'1\',
  `is_dummy` tinyint(1) NOT NULL DEFAULT \'0\',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di cancellazione (soft delete)\',
  `created_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di creazione del record\',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di ultimo aggiornamento\',
  PRIMARY KEY (`id`),
  KEY `clientis_customertype_id_foreign` (`customertype_id`),
  KEY `clientis_company_id_index` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Anagrafica Banche ed Istituti Finanziari (Clienti del mediatore)\';');

        DB::unprepared('CREATE TABLE `fornitoris` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'ID univoco del fornitore/agente\',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome del referente\',
  `stipulated_at` date DEFAULT NULL COMMENT \'Data stipula contratto collaborazione\',
  `pec` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Indirizzo Posta Elettronica Certificata\',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione\',
  `email_private` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supervisor_type` enum(\'no\',\'si\',\'filiale\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'no\' COMMENT \'Se supervisore indicare e specificare se di filiale\',
  `oam` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Oam\',
  `oam_at` date DEFAULT NULL COMMENT \'Data iscrizione OAM\',
  `oam_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Denominazione sociale registrata in OAM\',
  `numero_iscrizione_rui` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero iscrizione OAM\',
  `ivass` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice di iscrizione IVASS\',
  `ivass_at` date DEFAULT NULL COMMENT \'Data iscrizione IVASS\',
  `dismissed_at` date DEFAULT NULL COMMENT \'Data cessazione rapporto\',
  `ivass_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Denominazione IVASS\',
  `ivass_section` enum(\'A\',\'B\',\'C\',\'D\',\'E\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Sezione IVASS\',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Agente / Mediatore / Consulente / Call Center\',
  `is_active` tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'Indica se agente è attualmente convenzionato\',
  `is_art108` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Esente art. 108 - ex art. 128-novies TUB\',
  `company_branch_id` int unsigned DEFAULT NULL COMMENT \'Filiale di riferimento\',
  `coordinated_type` int unsigned DEFAULT NULL COMMENT \'ID del dipendente coordinatore\',
  `coordinated_id` int unsigned DEFAULT NULL COMMENT \'ID dell\'\'agente coordinatore\',
  `user_id` bigint unsigned DEFAULT NULL COMMENT \'ID dell\'\'utente collegato\',
  `oam_dismissed_at` date DEFAULT NULL COMMENT \'Data revoca OAM\',
  `welcome_bonus` decimal(10,2) DEFAULT NULL COMMENT \'Premio benvenuto\',
  `campagna` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice campagna\',
  `available_at` date DEFAULT NULL COMMENT \'Data disponibilità agente\',
  `budget` decimal(10,2) DEFAULT NULL COMMENT \'Budget agente\',
  `codice` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `natoil` date DEFAULT NULL COMMENT \'Data di nascita\',
  `indirizzo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Indirizzo\',
  `comune` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Comune di residenza\',
  `cap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice di avviamento postale\',
  `prov` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Provincia\',
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero di telefono\',
  `coordinatore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome del coordinatore di riferimento\',
  `piva` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Partita IVA\',
  `cf` char(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice fiscale\',
  `nomecoge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome per la contabilità\',
  `nomefattura` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome da utilizzare in fattura\',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anticipo` decimal(15,2) DEFAULT NULL COMMENT \'Quota fissa mensile erogata come anticipo provvigionale\',
  `enasarco` enum(\'no\',\'monomandatario\',\'plurimandatario\',\'societa\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'plurimandatario\' COMMENT \'Tipo di mandato ENASARCO\',
  `anticipo_residuo` decimal(15,2) DEFAULT NULL COMMENT \'Debito residuo dell\'\'agente verso il mediatore per anticipi da recuperare\',
  `contributo` decimal(15,2) DEFAULT NULL COMMENT \'Importo del contributo spese\',
  `contributo_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'Contributo spese\' COMMENT \'Descrizione del contributo spese\',
  `anticipo_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'Anticipo attuale\' COMMENT \'Descrizione dell\'\'anticipo\',
  `issubfornitore` tinyint DEFAULT NULL COMMENT \'1 se l\'\'agente opera tramite un altro intermediario\',
  `operatore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iscollaboratore` tinyint(1) DEFAULT NULL COMMENT \'Indica se è un collaboratore (1) o meno (0)\',
  `isdipendente` tinyint(1) DEFAULT \'0\' COMMENT \'Indica se è un dipendente (1) o meno (0)\',
  `regione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `citta` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di cancellazione (soft delete)\',
  `created_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di creazione del record\',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di ultimo aggiornamento\',
  `company_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'5c044917-15b3-4471-90c9-38061fcca754\',
  `contributoperiodicita` smallint DEFAULT NULL COMMENT \'Frequenza addebito costi (1=Mensile, 3=Trimestrale, etc.)\',
  `contributodalmese` date DEFAULT NULL,
  `fornitorirole_id` int DEFAULT NULL,
  `branch_id` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `piva` (`piva`),
  KEY `company_id` (`company_id`),
  KEY `fornitorirole_id` (`fornitorirole_id`),
  CONSTRAINT `fornitoris_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `fornitoris_ibfk_2` FOREIGN KEY (`fornitorirole_id`) REFERENCES `fornitoriroles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Anagrafica Agenti di commercio / Collaboratori (Fornitori di servizi)\';');

        DB::unprepared('CREATE TABLE `blacklist_clienti_employees` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `data_inizio` date DEFAULT NULL,
  `data_fine` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blacklist_clienti_employees_cliente_id_foreign` (`cliente_id`),
  KEY `blacklist_clienti_employees_employee_id_cliente_id_index` (`employee_id`,`cliente_id`),
  CONSTRAINT `blacklist_clienti_employees_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `blacklist_clienti_fornitori` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fornitore_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `data_inizio` date DEFAULT NULL,
  `data_fine` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blacklist_clienti_fornitori_cliente_id_foreign` (`cliente_id`),
  KEY `blacklist_clienti_fornitori_fornitore_id_cliente_id_index` (`fornitore_id`,`cliente_id`),
  CONSTRAINT `blacklist_clienti_fornitori_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blacklist_clienti_fornitori_fornitore_id_foreign` FOREIGN KEY (`fornitore_id`) REFERENCES `fornitoris` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID intero autoincrementante\',
  `addressable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Classe del Modello collegato (es. App\\\\Models\\\\Client)\',
  `addressable_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'ID del Modello (VARCHAR 36 per supportare sia UUID che Integer)\',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione\',
  `numero` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero civico o identificativo indirizzo\',
  `street` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Via e numero civico\',
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Città o Comune\',
  `zip_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'CAP (Codice di Avviamento Postale)\',
  `address_type_id` bigint unsigned DEFAULT NULL COMMENT \'Relazione con tipologia indirizzo\',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'Data inserimento indirizzo\',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'Data ultimo aggiornamento\',
  PRIMARY KEY (`id`),
  KEY `addresses_addressable_type_addressable_id_index` (`addressable_type`,`addressable_id`),
  KEY `addresses_address_type_id_foreign` (`address_type_id`),
  CONSTRAINT `addresses_address_type_id_foreign` FOREIGN KEY (`address_type_id`) REFERENCES `address_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `tipoprodotto_sub` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipoprodotto_id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Tipologia di prodotto finanziario\',
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vincoli` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT \'1\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `tipoprodotto_id` (`tipoprodotto_id`),
  CONSTRAINT `tipoprodotto_sub_ibfk_1` FOREIGN KEY (`tipoprodotto_id`) REFERENCES `tipoprodotto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `tipoprodotto_sub_constraints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipoprodotto_id` int DEFAULT NULL COMMENT \'ID Prodotto (collegato a tipoprodotto). Se NULL, si applica a tutti i prodotti.\',
  `tipoprodotto_sub_id` int DEFAULT NULL COMMENT \'ID Sottoprodotto (collegato a tipoprodotto_sub). Se NULL, si applica a tutti i sottoprodotti del prodotto selezionato.\',
  `clienti_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'UUID della Banca/Istituto erogante (collegato alla tabella clienti). Se NULL, si applica a tutte le banche/clienti.\',
  `role_id` int DEFAULT NULL COMMENT \'ID Ruolo/Livello dell\'\'agente (es. Senior, Junior). Se NULL, si applica a tutta la rete.\',
  `min_age` int DEFAULT NULL COMMENT \'Età minima del richiedente alla firma.\',
  `max_age_at_maturity` int DEFAULT NULL COMMENT \'Età massima consentita alla scadenza del finanziamento.\',
  `min_amount` decimal(12,2) DEFAULT NULL COMMENT \'Importo minimo erogabile.\',
  `max_amount` decimal(12,2) DEFAULT NULL COMMENT \'Importo massimo erogabile.\',
  `min_duration_months` int DEFAULT NULL COMMENT \'Durata minima in mesi.\',
  `max_duration_months` int DEFAULT NULL COMMENT \'Durata massima in mesi.\',
  `min_employment_months` int DEFAULT NULL COMMENT \'Anzianità lavorativa minima richiesta.\',
  `max_debt_to_income_ratio` decimal(5,2) DEFAULT NULL COMMENT \'Rapporto rata/reddito massimo % (es. 33.33).\',
  `max_ltv_percentage` decimal(5,2) DEFAULT NULL COMMENT \'LTV massimo consentito per mutui %.\',
  `allowed_employment_types` json DEFAULT NULL COMMENT \'Tipi di impiego accettati JSON (es. ["indeterminato", "pensionato"]).\',
  `additional_rules_json` json DEFAULT NULL COMMENT \'Ulteriori vincoli dinamici in formato chiave-valore. Es: {"requires_co_signer": true, "blocked_ateco_codes": ["96.09"]}\',
  `additional_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Eventuali note descrittive testuali dei vincoli per l\'\'operatore.\',
  `is_active` tinyint DEFAULT \'1\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_commission_hierarchy` (`tipoprodotto_id`,`tipoprodotto_sub_id`,`clienti_id`),
  KEY `commission_rules_tipoprodotto_sub_id_foreign` (`tipoprodotto_sub_id`),
  KEY `commission_rules_clienti_id_foreign` (`clienti_id`),
  CONSTRAINT `commission_rules_clienti_id_foreign` FOREIGN KEY (`clienti_id`) REFERENCES `clientis` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commission_rules_tipoprodotto_id_foreign` FOREIGN KEY (`tipoprodotto_sub_id`) REFERENCES `tipoprodotto` (`id`) ON DELETE SET NULL,
  CONSTRAINT `commission_rules_tipoprodotto_sub_id_foreign` FOREIGN KEY (`tipoprodotto_sub_id`) REFERENCES `tipoprodotto_sub` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Matrice delle regole provvigionali. Gestisce la gerarchia a cascata (Prodotto -> Sottoprodotto -> Cliente UUID) con eliminazione logica.\';');
    }

    public function down(): void
    {
        Schema::dropIfExists('tipoprodotto_sub_constraints');
        Schema::dropIfExists('tipoprodotto_sub');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('blacklist_clienti_fornitori');
        Schema::dropIfExists('blacklist_clienti_employees');
        Schema::dropIfExists('fornitoris');
        Schema::dropIfExists('clientis');
    }
};
