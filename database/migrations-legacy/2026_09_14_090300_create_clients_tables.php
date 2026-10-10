<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline della tabella legacy "clients", "client_mandates", "client_relations"
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

        DB::unprepared('CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID intero autoincrementante\',
  `company_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'5c044917-15b3-4471-90c9-38061fcca754\',
  `is_person` tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'Persona fisica (true) o giuridica (false)\',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Cognome (se persona fisica) o Ragione Sociale (se giuridica)\',
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome persona fisica\',
  `tax_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice Fiscale o Partita IVA del cliente\',
  `vat_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Email di contatto principale\',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Recapito telefonico\',
  `website` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_pep` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Persona Politicamente Esposta\',
  `client_type_id` bigint unsigned DEFAULT NULL COMMENT \'Classificazione cliente\',
  `is_sanctioned` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Presente in liste antiterrorismo/blacklists\',
  `is_remote_interaction` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Operatività a distanza = Rischio più alto\',
  `general_consent_at` timestamp NULL DEFAULT NULL COMMENT \'Consenso generale al trattamento base\',
  `privacy_policy_read_at` timestamp NULL DEFAULT NULL COMMENT \'Data presa visione informativa Art.13\',
  `consent_special_categories_at` timestamp NULL DEFAULT NULL COMMENT \'Consenso dati sanitari/giudiziari per polizze/CQS\',
  `consent_sic_at` timestamp NULL DEFAULT NULL COMMENT \'Consenso interrogazione CRIF/CTC/Experian\',
  `consent_marketing_at` timestamp NULL DEFAULT NULL COMMENT \'Consenso comunicazioni commerciali e newsletter\',
  `consent_profiling_at` timestamp NULL DEFAULT NULL COMMENT \'Consenso profilazione abitudini di consumo/spesa\',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'raccolta_dati\' COMMENT \'raccolta_dati, valutazione_aml, approvata, sos_inviata, chiusa\',
  `is_company` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'True se il cliente è un\'\'azienda fornitore\',
  `is_lead` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'True se è un lead non ancora convertito\',
  `leadsource_id` bigint unsigned DEFAULT NULL COMMENT \'ID del client che ha fornito il lead\',
  `acquired_at` timestamp NULL DEFAULT NULL COMMENT \'Data di acquisizione del contatto\',
  `contoCOGE` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Conto COGE\',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'Data acquisizione cliente\',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT \'Ultima modifica anagrafica\',
  `privacy_consent` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Consenso privacy del cliente\',
  `is_client` tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'contraente contratto\',
  `subfornitori` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Subfornitori da comunicare per gradimento\',
  `is_requiredApprovation` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Da far approvare per gradimento\',
  `is_approved` tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'Approvata per gradimento\',
  `is_anonymous` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Cliente anonimo (non comunicabile)\',
  `blacklist_at` timestamp NULL DEFAULT NULL COMMENT \'Data inserimento in blacklist\',
  `blacklisted_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'ID dell\'\'utente che ha inserito in blacklist (senza link esterni)\',
  `salary` decimal(10,2) DEFAULT NULL COMMENT \'Retribuzione annuale del cliente\',
  `salary_quote` decimal(10,2) DEFAULT NULL COMMENT \'Quota retribuzione per calcoli finanziari\',
  `is_art108` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Esente art. 108 - ex art. 128-novies TUB\',
  `is_consultant_gdpr` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Consulente ai fini GDPR\',
  `privacy_contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Email contatto privacy\',
  `dpo_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Email DPO\',
  `is_iso27001_certified` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Certificazione ISO 27001\',
  `is_dummy` tinyint(1) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`id`),
  KEY `clients_company_id_index` (`company_id`),
  KEY `clients_client_type_id_index` (`client_type_id`),
  KEY `clients_leadsource_id_index` (`leadsource_id`),
  KEY `clients_blacklist_at_index` (`blacklist_at`),
  KEY `clients_is_anonymous_index` (`is_anonymous`),
  KEY `clients_is_approved_index` (`is_approved`),
  CONSTRAINT `clients_client_type_id_foreign` FOREIGN KEY (`client_type_id`) REFERENCES `client_types` (`id`),
  CONSTRAINT `clients_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clients_leadsource_id_foreign` FOREIGN KEY (`leadsource_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `client_mandates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco mandato cliente\',
  `client_id` bigint unsigned DEFAULT NULL COMMENT \'Riferimento al cliente coinvolto\',
  `numero_mandato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero identificativo mandato\',
  `data_firma_mandato` date DEFAULT NULL COMMENT \'Innesca Instaurazione Rapporto AUI\',
  `data_scadenza_mandato` date DEFAULT NULL COMMENT \'Innesca Chiusura Rapporto AUI (se non erogato prima)\',
  `importo_richiesto_mandato` decimal(15,2) DEFAULT NULL COMMENT \'Importo massimo richiesto nel mandato\',
  `scopo_finanziamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Scopo del finanziamento (es. Acquisto Prima Casa, Liquidità)\',
  `data_consegna_trasparenza` date DEFAULT NULL COMMENT \'Deve essere <= data_firma\',
  `stato` enum(\'attivo\',\'concluso_con_successo\',\'scaduto\',\'revocato\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'attivo\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_mandates_client_id_foreign` (`client_id`),
  CONSTRAINT `client_mandates_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `client_relations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco relazione cliente\',
  `company_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'ID società persona giuridica\',
  `client_id` bigint unsigned NOT NULL COMMENT \'ID persona fisica cliente\',
  `shares_percentage` decimal(5,2) DEFAULT NULL COMMENT \'Percentuale quote possedute\',
  `is_titolare` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Se è titolare/socio di maggioranza\',
  `client_type_id` int unsigned DEFAULT NULL COMMENT \'Tipo di cliente\',
  `data_inizio_ruolo` date DEFAULT NULL COMMENT \'Data inizio ruolo\',
  `data_fine_ruolo` date DEFAULT NULL COMMENT \'Data fine ruolo\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_relations_company_id_foreign` (`company_id`),
  KEY `client_relations_client_id_foreign` (`client_id`),
  CONSTRAINT `client_relations_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');
    }

    public function down(): void
    {
        Schema::dropIfExists('client_relations');
        Schema::dropIfExists('client_mandates');
        Schema::dropIfExists('clients');
    }
};
