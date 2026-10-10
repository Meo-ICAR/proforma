<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabelle proprie di Proforma (provvigioni, proforma, fatture, ENASARCO, COGE...). Le CREATE TABLE vengono dal dump dello
 * schema live, con tre differenze dovute al pacchetto meo-icar/unico-core: le colonne che richiamano `clientis`, `fornitoris` e
 * `companies` sono interi (`bigint unsigned`) invece di UUID, e `provvigioni.id_pratica` resta il codice della pratica
 * che arriva dall'API (`pratiches.codice_pratica`, ad es. QT06585). Il legame vero è `provvigioni.pratica_id` → `pratiches.id`,
 * che valorizza l'import.
 * Le tabelle del pacchetto (utenti, aziende, clienti, fornitori, pratiche...) si creano con le sue migration, che vengono prima.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Il dump è stato preso con sessione UTC: senza impostarla anche qui, MySQL interpreterebbe i DEFAULT letterali
        // dei timestamp nel fuso orario della sessione corrente.
        DB::unprepared("SET time_zone = '+00:00'");

        DB::unprepared('CREATE TABLE `provvigioni_statos` (
  `stato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Inserito\' COMMENT \'Stato della provvigione\',
  PRIMARY KEY (`stato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;');

        DB::unprepared('CREATE TABLE `fatturas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco del proforma\',
  `stato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Inserito\' COMMENT \'Stato del proforma (es. Inserito, Inviato, Pagato)\',
  `clienti_id` bigint unsigned DEFAULT NULL COMMENT \'Riferimento al fornitore/agente\',
  `anticipo` decimal(15,2) DEFAULT NULL COMMENT \'Importo dell\'\'anticipo\',
  `anticipo_descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione dell\'\'anticipo\',
  `compenso` decimal(15,2) DEFAULT NULL COMMENT \'Importo del compenso\',
  `compenso_descrizione` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Descrizione dettagliata del compenso\',
  `contributo` decimal(15,2) DEFAULT NULL COMMENT \'Importo del contributo\',
  `contributo_descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione del contributo\',
  `annotation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note e annotazioni aggiuntive\',
  `emailsubject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Oggetto dell\'\'email di invio\',
  `emailto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Indirizzo email del destinatario\',
  `emailbody` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Corpo dell\'\'email di invio\',
  `emailfrom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Indirizzo email del mittente\',
  `sended_at` datetime DEFAULT NULL COMMENT \'Data e ora di invio del proforma\',
  `paid_at` datetime DEFAULT NULL COMMENT \'Data e ora di pagamento\',
  `delta` decimal(15,2) DEFAULT NULL COMMENT \'Differenza/variazione di importo\',
  `anticipo_residuo` decimal(15,2) DEFAULT NULL,
  `delta_annotation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note relative alla variazione di importo\',
  `created_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di creazione del record\',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di ultimo aggiornamento\',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di cancellazione (soft delete)\',
  PRIMARY KEY (`id`),
  KEY `clienti_id` (`clienti_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Testate delle fatture attive emesse o ricevute\';');

        DB::unprepared('CREATE TABLE `proformas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco del proforma\',
  `stato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Inserito\' COMMENT \'Stato del proforma (es. Inserito, Inviato, Pagato)\',
  `fornitori_id` bigint unsigned DEFAULT NULL COMMENT \'Riferimento al fornitore/agente\',
  `anticipo` decimal(15,2) DEFAULT NULL COMMENT \'Importo dell\'\'anticipo\',
  `anticipo_descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione dell\'\'anticipo\',
  `compenso` decimal(15,2) DEFAULT NULL COMMENT \'Importo del compenso\',
  `compenso_descrizione` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Descrizione dettagliata del compenso\',
  `contributo` decimal(15,2) DEFAULT NULL COMMENT \'Importo del contributo\',
  `contributo_descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione del contributo\',
  `annotation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note e annotazioni aggiuntive\',
  `emailsubject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Oggetto dell\'\'email di invio\',
  `emailto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Indirizzo email del destinatario\',
  `emailbody` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Corpo dell\'\'email di invio\',
  `emailfrom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Indirizzo email del mittente\',
  `sended_at` datetime DEFAULT NULL COMMENT \'Data e ora di invio del proforma\',
  `paid_at` datetime DEFAULT NULL COMMENT \'Data valuta del bonifico effettuato all\'\'agente\',
  `delta` decimal(15,2) DEFAULT NULL COMMENT \'Differenza tra il calcolato dal sistema e l\'\'importo richiesto dall\'\'agente\',
  `anticipo_residuo` decimal(15,2) DEFAULT NULL,
  `delta_annotation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note relative alla variazione di importo\',
  `created_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di creazione del record\',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di ultimo aggiornamento\',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT \'Timestamp di cancellazione (soft delete)\',
  `invoiceable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoiceable_id` bigint DEFAULT NULL,
  `tipo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fornitori_id` (`fornitori_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Documenti proforma emessi dagli agenti verso il mediatore\';');

        DB::unprepared('CREATE TABLE `provvigioni` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Identificativo univoco della provvigione (UUID)\',
  `data_inserimento_compenso` date DEFAULT NULL COMMENT \'Data di inserimento del compenso\',
  `descrizione` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione della provvigione\',
  `tipo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Tipologia di provvigione\',
  `importo` decimal(15,2) DEFAULT NULL COMMENT \'Importo lordo della provvigione\',
  `data_status` date DEFAULT NULL COMMENT \'Data dell\'\'ultimo aggiornamento di stato del compenso\',
  `data_pagamento` date DEFAULT NULL COMMENT \'Data dell\'\'effettivo incasso o pagamento\',
  `importo_effettivo` decimal(15,2) DEFAULT NULL COMMENT \'Importo netto dopo eventuali storni o rettifiche\',
  `stato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Stato della provvigione\',
  `status_compenso` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Stato di maturazione del compenso (es. Maturato, Incassato)\',
  `n_fattura` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero di fattura fiscale associata\',
  `data_fattura` date DEFAULT NULL COMMENT \'Data di emissione della fattura fiscale\',
  `denominazione_riferimento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Ragione sociale del referente\',
  `entrata_uscita` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Entrata: provvigione attiva da Banca; Uscita: provvigione passiva per Agente\',
  `id_pratica` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'ID della pratica associata\',
  `segnalatore` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome del segnalatore\',
  `istituto_finanziario` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome della banca/istituto finanziario\',
  `piva` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Partita IVA del referente o dell\'\'agente\',
  `cf` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice fiscale del referente o del cliente\',
  `annullato` tinyint(1) DEFAULT \'0\' COMMENT \'Flag di annullamento o storno provvigionale (1 = Annullato, 0 = Attivo)\',
  `coordinamento` tinyint(1) DEFAULT NULL COMMENT \'Flag per provvigioni derivanti da attività di gestione rete/team\',
  `iscliente` tinyint(1) DEFAULT NULL COMMENT \'Flag di identificazione cliente diretto (1 = Cliente, 0 = Non cliente)\',
  `proforma_id` bigint unsigned DEFAULT NULL COMMENT \'ID del documento di proforma associato\',
  `legacy_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'ID ereditato dal vecchio sistema\',
  `invoice_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Numero fattura associata\',
  `cognome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Cognome del cliente\',
  `quota` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Percentuale o quota fissa spettante sul totale pratica\',
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome del cliente\',
  `fonte` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Fonte della provvigione\',
  `tipo_pratica` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Tipologia di pratica\',
  `data_inserimento_pratica` date DEFAULT NULL COMMENT \'Data di inserimento della pratica\',
  `data_stipula` date DEFAULT NULL COMMENT \'Data di stipula del contratto\',
  `prodotto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Prodotto finanziario\',
  `macrostatus` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Macro stato della pratica\',
  `status_pratica` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Stato dettagliato della pratica\',
  `status_pagamento` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Stato di saldo del compenso (es. Saldato, In sospeso)\',
  `data_status_pratica` date DEFAULT NULL COMMENT \'Data dell\'\'ultimo cambio stato pratica\',
  `montante` decimal(15,2) DEFAULT NULL COMMENT \'Importo totale del finanziamento\',
  `importo_erogato` decimal(15,2) DEFAULT NULL COMMENT \'Importo effettivamente erogato\',
  `created_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora di creazione del record\',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora dell\'\'ultimo aggiornamento\',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora di cancellazione logica (Soft Delete)\',
  `sended_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora di invio della provvigione\',
  `received_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora di ricezione della provvigione\',
  `erogated_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora di erogazione della pratica collegata\',
  `paided_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora di pagamento della provvigione\',
  `fattura_id` bigint unsigned DEFAULT NULL COMMENT \'ID della fattura contabile definitiva collegata\',
  `upload_at` timestamp NOT NULL DEFAULT \'2026-04-03 03:54:45\' COMMENT \'Data e ora di caricamento a sistema del record\',
    `pratica_id` bigint unsigned DEFAULT NULL COMMENT \'Pratica collegata (pratiches.id): la valorizza l\'\'import, che parte dal codice in id_pratica\',
  PRIMARY KEY (`id`),
  KEY `pratica_id` (`pratica_id`),
  KEY `proforma_id` (`proforma_id`),
  KEY `id_pratica` (`id_pratica`),
  KEY `fattura_id` (`fattura_id`),
  KEY `idx_performance_calcolo` (`stato`,`entrata_uscita`,`data_status`,`importo`),
  CONSTRAINT `fk_provvigioni_stato` FOREIGN KEY (`stato`) REFERENCES `provvigioni_statos` (`stato`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `provvigioni_ibfk_1` FOREIGN KEY (`proforma_id`) REFERENCES `proformas` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `provvigioni_pratica_id_foreign` FOREIGN KEY (`pratica_id`) REFERENCES `pratiches` (`id`),
  CONSTRAINT `provvigioni_ibfk_3` FOREIGN KEY (`fattura_id`) REFERENCES `fatturas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Singole righe provvigionali attive (Banca -> Mediatore) e passive (Mediatore -> Agente)\';');

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
  `clienti_id` bigint unsigned DEFAULT NULL COMMENT \'Riferimento al cliente (se fattura attiva)\',
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
  `company_id` bigint unsigned DEFAULT NULL,
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
  `company_id` bigint unsigned NOT NULL,
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

        // Colonne di `proformas` presenti nel database live ma non nel dump di partenza (usate da App\Models\Proforma).
        Schema::table('proformas', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->nullable()->index()->comment('Cliente/consulente (tabella clients) controparte del proforma, quando applicabile');
            $table->decimal('welcome', 15, 2)->nullable()->comment('Importo del welcome bonus');
            $table->string('welcome_description')->nullable()->comment('Causale del welcome bonus');
            $table->decimal('spese', 15, 2)->nullable()->comment('Importo delle spese');
            $table->string('spese_description')->nullable()->comment('Causale delle spese');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (array_reverse([
            'provvigioni_statos', 'fatturas', 'proformas', 'provvigioni', 'coges', 'compensos', 'enasarcos', 'firrs', 'vcoge', 'venasarcotot', 'venasarcotrimestre', 'invoiceins', 'invoices', 'purchase_invoices', 'sales_invoices',
        ]) as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }
};
