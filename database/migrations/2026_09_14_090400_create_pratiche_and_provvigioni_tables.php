<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline della tabella legacy "pratiches", "client_pratiches", "pratica_document_requests", "pratica_status_history", "fatturas", "proformas", "provvigioni"
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

        // Il dump sorgente di pratiches.upload_at/provvigioni.upload_at è stato
        // preso con sessione UTC (SET time_zone = '+00:00'): senza impostarla
        // anche qui, MySQL interpreterebbe il valore letterale di DEFAULT nel
        // fuso orario della sessione corrente, spostando l'istante reale.
        DB::unprepared("SET time_zone = '+00:00'");

        DB::unprepared('CREATE TABLE `pratiches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'ID univoco pratica\',
  `codice_pratica` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice identificativo della pratica\',
  `nome_cliente` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome del cliente\',
  `cognome_cliente` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Cognome del cliente\',
  `codice_fiscale` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice fiscale del cliente\',
  `denominazione_agente` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Ragione sociale dell\'\'agente/rappresentante\',
  `partita_iva_agente` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Partita IVA dell\'\'agente\',
  `denominazione_banca` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Banca erogatrice del finanziamento\',
  `tipo_prodotto` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Categoria finanziaria (es. Cessione del Quinto, Mutuo, Prestito Personale)\',
  `denominazione_prodotto` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Nome/descrizione del prodotto finanziario\',
  `data_inserimento_pratica` date DEFAULT NULL COMMENT \'Data di creazione della pratica\',
  `stato_pratica` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Stato attuale della pratica\',
  `created_at` datetime DEFAULT NULL COMMENT \'Data e ora di creazione del record\',
  `updated_at` datetime DEFAULT NULL COMMENT \'Data e ora dell\'\'ultimo aggiornamento del record\',
  `rata` decimal(10,2) DEFAULT NULL COMMENT \'Importo della rata mensile del finanziamento\',
  `erogato` decimal(10,2) DEFAULT NULL COMMENT \'Importo lordo effettivamente erogato\',
  `nrate` int DEFAULT NULL COMMENT \'Numero totale di rate del piano di ammortamento\',
  `sended_at` date DEFAULT NULL COMMENT \'Data di invio della pratica in istruttoria alla banca\',
  `approved_at` date DEFAULT NULL COMMENT \'Data di approvazione/delibera da parte della banca\',
  `erogated_at` date DEFAULT NULL COMMENT \'Data di effettivo perfezionamento ed erogazione\',
  `amount` decimal(10,2) DEFAULT NULL COMMENT \'Importo del finanziamento richiesto in fase di istruttoria\',
  `net` decimal(10,2) DEFAULT NULL COMMENT \'Importo netto spettante\',
  `is_notowned` tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'Flag indicante se la pratica appartiene a rete esterna (1 = Sì, 0 = No)\',
  `upload_at` timestamp NOT NULL DEFAULT \'2026-04-03 03:54:45\' COMMENT \'Data e ora di caricamento a sistema\',
  `rejected_at` date DEFAULT NULL COMMENT \'Data di rifiuto o annullamento della pratica\',
  `abi` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Codice ABI dell\'\'istituto bancario\',
  `abi_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Denominazione dell\'\'istituto bancario da codice ABI\',
  PRIMARY KEY (`id`),
  KEY `idx_cliente_cf` (`cognome_cliente`),
  KEY `fk_pratiches_stato_pratica` (`stato_pratica`),
  KEY `fk_pratiches_tipo_prodotto` (`tipo_prodotto`),
  CONSTRAINT `fk_pratiches_stato_pratica` FOREIGN KEY (`stato_pratica`) REFERENCES `pratiches_statos` (`stato_pratica`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pratiches_tipo_prodotto` FOREIGN KEY (`tipo_prodotto`) REFERENCES `tipoprodotto` (`tipo_prodotto`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Registro delle pratiche di finanziamento/mutuo caricate\';');

        DB::unprepared('CREATE TABLE `client_pratiches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco del legame\',
  `pratiche_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Riferimento alla pratica (CORRETTO: varchar)\',
  `client_id` bigint unsigned NOT NULL COMMENT \'Riferimento al cliente coinvolto\',
  `role` enum(\'intestatario\',\'cointestatario\',\'garante\',\'terzo_datore\') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'intestatario\' COMMENT \'Ruolo legale del cliente nella pratica\',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Descrizione\',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note specifiche sul ruolo\',
  `purpose_of_relationship` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Es: Acquisto prima casa\',
  `funds_origin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `client_pratiche_ibfk_1` (`pratiche_id`),
  KEY `client_pratiche_ibfk_2` (`client_id`),
  CONSTRAINT `client_pratiche_ibfk_1` FOREIGN KEY (`pratiche_id`) REFERENCES `pratiches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_pratiche_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        DB::unprepared('CREATE TABLE `pratica_document_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pratica_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Collegamento alla pratica\',
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Nome/Tipo di documento richiesto (es. Cedolino, Certificato di servizio)\',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Istruzioni dettagliate o motivo della richiesta della banca\',
  `requested_by` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'bank\' COMMENT \'Chi ha originato la richiesta: bank (banca), backoffice (interno), agent (agente)\',
  `assigned_to_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'agent\' COMMENT \'A chi spetta l\'\'azione: agent (agente), client (cliente)\',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'pending\' COMMENT \'Stato: pending (attesa), uploaded (caricato), approved (accettato), rejected (rifiutato)\',
  `share_with_client` tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'Se 1, la richiesta è visibile e richiedibile direttamente al cliente finale\',
  `requested_at` datetime NOT NULL COMMENT \'Quando è stato richiesto\',
  `due_date` date DEFAULT NULL COMMENT \'Scadenza consigliata per l\'\'invio prima della decadenza\',
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Percorso del file una volta caricato\',
  `rejected_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Motivo dell\'\'eventuale rifiuto del documento (se non idoneo)\',
  `agent_notified_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora in cui l\'\'agente è stato notificato\',
  `client_notified_at` timestamp NULL DEFAULT NULL COMMENT \'Data e ora in cui il cliente è stato notificato (es. via email/SMS)\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_doc_request_pratica` (`pratica_id`),
  CONSTRAINT `fk_doc_request_pratica` FOREIGN KEY (`pratica_id`) REFERENCES `pratiches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Richieste di documentazione integrativa per le pratiche in istruttoria.\';');

        DB::unprepared('CREATE TABLE `pratica_status_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pratica_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'ID della pratica (riferito a pratiches.id)\',
  `status_from` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Stato precedente (NULL se è il primo inserimento)\',
  `status_to` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT \'Nuovo stato impostato\',
  `changed_at` datetime NOT NULL COMMENT \'Data e ora esatta del cambio di stato\',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'manual\' COMMENT \'Origine del cambio: manual, api_banca, import_excel\',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT \'Note o messaggi di errore restituiti dal portale bancario\',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_history_pratica` (`pratica_id`),
  CONSTRAINT `fk_history_pratica` FOREIGN KEY (`pratica_id`) REFERENCES `pratiches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Storico dei cambi di stato delle pratiche per la pipeline di avanzamento.\';');

        DB::unprepared('CREATE TABLE `fatturas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT \'ID univoco del proforma\',
  `stato` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT \'Inserito\' COMMENT \'Stato del proforma (es. Inserito, Inviato, Pagato)\',
  `clienti_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Riferimento al fornitore/agente\',
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
  `fornitori_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT \'Riferimento al fornitore/agente\',
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
  PRIMARY KEY (`id`),
  KEY `proforma_id` (`proforma_id`),
  KEY `id_pratica` (`id_pratica`),
  KEY `fattura_id` (`fattura_id`),
  KEY `idx_performance_calcolo` (`stato`,`entrata_uscita`,`data_status`,`importo`),
  CONSTRAINT `fk_provvigioni_stato` FOREIGN KEY (`stato`) REFERENCES `provvigioni_statos` (`stato`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `provvigioni_ibfk_1` FOREIGN KEY (`proforma_id`) REFERENCES `proformas` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `provvigioni_ibfk_2` FOREIGN KEY (`id_pratica`) REFERENCES `pratiches` (`id`),
  CONSTRAINT `provvigioni_ibfk_3` FOREIGN KEY (`fattura_id`) REFERENCES `fatturas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT=\'Singole righe provvigionali attive (Banca -> Mediatore) e passive (Mediatore -> Agente)\';');
    }

    public function down(): void
    {
        Schema::dropIfExists('provvigioni');
        Schema::dropIfExists('proformas');
        Schema::dropIfExists('fatturas');
        Schema::dropIfExists('pratica_status_history');
        Schema::dropIfExists('pratica_document_requests');
        Schema::dropIfExists('client_pratiches');
        Schema::dropIfExists('pratiches');
    }
};
