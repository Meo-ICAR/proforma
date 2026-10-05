<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline della tabella legacy "invoiceins", "invoices", "purchase_invoices", "sales_invoices"
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

        DB::unprepared('CREATE TABLE `invoiceins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco del record di importazione\',
  `tipo_di_documento` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Tipologia del documento (es. Fattura, Nota di credito)\',
  `nr_documento` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero progressivo del documento\',
  `nr_fatt_acq_registrata` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero di fattura acquisto registrata\',
  `nr_nota_cr_acq_registrata` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero di nota di credito acquisto registrata\',
  `data_ricezione_fatt` date DEFAULT NULL COMMENT \'Data di ricezione della fattura\',
  `codice_td` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice tipo documento\',
  `nr_cliente_fornitore` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice identificativo del fornitore\',
  `nome_fornitore` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Ragione sociale del fornitore\',
  `partita_iva` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Partita IVA del fornitore\',
  `nr_documento_fornitore` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero del documento del fornitore\',
  `allegato` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Allegati al documento\',
  `data_documento_fornitore` date DEFAULT NULL COMMENT \'Data del documento del fornitore\',
  `data_primo_pagamento_prev` date DEFAULT NULL COMMENT \'Data prevista per il primo pagamento\',
  `imponibile_iva` decimal(15,2) DEFAULT NULL COMMENT \'Imponibile IVA\',
  `importo_iva` decimal(15,2) DEFAULT NULL COMMENT \'Importo IVA\',
  `importo_totale_fornitore` decimal(15,2) DEFAULT NULL COMMENT \'Importo totale della fattura fornitore\',
  `importo_totale_collegato` decimal(15,2) DEFAULT NULL COMMENT \'Importo totale collegato\',
  `data_ora_invio_ricezione` datetime DEFAULT NULL COMMENT \'Data e ora di invio/ricezione\',
  `stato` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Stato del documento\',
  `id_documento` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'ID univoco del documento\',
  `id_sdi` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'ID Sistema di Interscambio (per fatture elettroniche)\',
  `nr_lotto_documento` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero lotto del documento\',
  `nome_file_doc_elettronico` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome file documento elettronico\',
  `filtro_carichi` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Filtro per carichi\',
  `cdc_codice` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice centro di costo\',
  `cod_colleg_dimen_2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice collegamento dimensione 2\',
  `allegato_in_file_xml` tinyint(1) DEFAULT NULL COMMENT \'Indica se è presente un allegato nel file XML (1) o meno (0)\',
  `note_1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note aggiuntive 1\',
  `note_2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note aggiuntive 2\',
  `created_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di creazione del record\',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di ultimo aggiornamento\',
  PRIMARY KEY (`id`) COMMENT \'Chiave primaria della tabella di importazione fatture\',
  KEY `partita_iva` (`partita_iva`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Area di staging per l\'\'importazione di fatture passive tramite XML/SDI\';');

        DB::unprepared('CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco della fattura\',
  `competenza` year DEFAULT \'2025\',
  `fornitore_piva` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Partita IVA del fornitore (per fatture passive)\',
  `fornitore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Ragione sociale del fornitore (per fatture passive)\',
  `total_amount` decimal(10,2) DEFAULT NULL COMMENT \'Importo totale della fattura (al netto di IVA)\',
  `tax_amount` decimal(10,2) DEFAULT NULL COMMENT \'Importo IVA\',
  `importo_iva` decimal(10,2) DEFAULT NULL,
  `importo_totale_fornitore` decimal(10,2) DEFAULT NULL,
  `delta` decimal(15,2) DEFAULT NULL COMMENT \'Eventuale scostamento/variazione\',
  `clienti_id` varchar(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Riferimento al cliente (se fattura attiva)\',
  `nr_documento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero fattura\',
  `invoice_date` datetime DEFAULT NULL COMMENT \'Data di emissione fattura\',
  `sended_at` datetime DEFAULT NULL COMMENT \'Data di primo invio al cliente\',
  `sended2_at` datetime DEFAULT NULL COMMENT \'Data di secondo invio (sollecito)\',
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'EUR\' COMMENT \'Valuta (es. EUR)\',
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Modalità di pagamento\',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT \'imported\' COMMENT \'Stato della fattura (es. imported, sent, paid)\',
  `paid_at` date DEFAULT NULL COMMENT \'Data di pagamento\',
  `isreconiled` tinyint(1) DEFAULT NULL COMMENT \'Flag di avvenuta associazione tra documento contabile e movimenti provvigionali\',
  `is_notenasarco` tinyint(1) DEFAULT \'0\',
  `xml_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Dump del contenuto tecnico della fattura elettronica\',
  `created_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di creazione del record\',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di ultimo aggiornamento\',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di cancellazione (soft delete)\',
  `coge` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice contabile COGE\',
  PRIMARY KEY (`id`) COMMENT \'Chiave primaria della tabella fatture\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'fatture\';');

        DB::unprepared('CREATE TABLE `purchase_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `amount_including_vat` decimal(10,2) DEFAULT NULL,
  `pay_to_cap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_to_country_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_date` date DEFAULT NULL,
  `location_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `printed_copies` int DEFAULT \'0\',
  `document_date` date DEFAULT NULL,
  `payment_condition_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `payment_method_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `residual_amount` decimal(10,2) DEFAULT NULL,
  `closed` tinyint DEFAULT \'0\',
  `cancelled` tinyint DEFAULT \'0\',
  `corrected` tinyint DEFAULT \'0\',
  `is_nopractice` tinyint(1) NOT NULL DEFAULT \'0\',
  `pay_to_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_to_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exchange_rate` decimal(10,4) DEFAULT NULL,
  `vat_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fiscal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoiceable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Type of model (Client, Agent, etc.)\',
  `invoiceable_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'ID of the related model\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_invoices_company_id_number_index` (`company_id`,`number`),
  KEY `purchase_invoices_company_id_supplier_index` (`company_id`,`supplier`),
  KEY `purchase_invoices_company_id_registration_date_index` (`company_id`,`registration_date`),
  KEY `purchase_invoices_invoiceable_type_invoiceable_id_index` (`invoiceable_type`,`invoiceable_id`),
  CONSTRAINT `purchase_invoices_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `sales_invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoiceable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Type of model (Client, Principal, etc.)\',
  `invoiceable_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'ID of the related model\',
  `number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `amount_including_vat` decimal(10,2) NOT NULL,
  `residual_amount` decimal(10,2) NOT NULL,
  `ship_to_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to_cap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_date` date NOT NULL,
  `agent_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cdc_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dimensional_link_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `printed_copies` int DEFAULT \'0\',
  `payment_condition_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT \'0\',
  `cancelled` tinyint(1) NOT NULL DEFAULT \'0\',
  `corrected` tinyint(1) NOT NULL DEFAULT \'0\',
  `is_nopractice` tinyint(1) NOT NULL DEFAULT \'0\',
  `email_sent` tinyint(1) NOT NULL DEFAULT \'0\',
  `email_sent_at` datetime DEFAULT NULL,
  `bill_to_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bill_to_city` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bill_to_province` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ship_to_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ship_to_city` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payment_method_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exchange_rate` decimal(10,2) DEFAULT NULL,
  `vat_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_residual_amount` decimal(10,2) DEFAULT NULL,
  `document_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit_note_linked` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_order` tinyint(1) NOT NULL DEFAULT \'0\',
  `supplier_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `purchase_invoice_origin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_to_sdi` tinyint(1) NOT NULL DEFAULT \'0\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_invoices_company_id_number_index` (`company_id`,`number`),
  KEY `sales_invoices_company_id_customer_number_index` (`company_id`,`customer_number`),
  KEY `sales_invoices_company_id_registration_date_index` (`company_id`,`registration_date`),
  KEY `sales_invoices_company_id_agent_code_index` (`company_id`,`agent_code`),
  KEY `sales_invoices_company_id_document_type_index` (`company_id`,`document_type`),
  KEY `sales_invoices_invoiceable_type_invoiceable_id_index` (`invoiceable_type`,`invoiceable_id`),
  CONSTRAINT `sales_invoices_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('invoiceins');
    }
};
