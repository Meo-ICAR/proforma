# Manuale Utente — Gestione Provvigioni Mediatori Creditizi

---

## Panoramica

Applicazione per mediatori creditizi che gestisce l'intero ciclo provvigionale: dall'import delle pratiche, all'emissione dei proforma, alla riconciliazione con le fatture, fino alla contabilità e ai contributi Enasarco.

---

## 1. Provvigioni

### 1.1 Lista Provvigioni (uscite)

La schermata principale mostra tutte le provvigioni passive (uscite verso produttori), escludendo quelle con importo zero.

**Colonne principali:** Stato, Produttore, Provvigione (€), Coordinamento, Data Perfezionamento, Cliente, Istituto Finanziario, Data Erogazione, Data Fattura, Pratica.

**Stati possibili:** `Inserito`, `Sospeso`, `Proforma`, `Pagato`, `Annullato`, `Escluso`, `Fatturato`, `Stornato`.

**Azioni su singola riga:**
- **Inverti stato** (icona freccia): alterna la provvigione tra `Inserito` e `Sospeso`. Utile per sospendere singole provvigioni prima dell'emissione del proforma.

**Azioni di massa (header):**
- **Emetti Proforma**: seleziona le provvigioni in stato `Inserito` e le aggrega in proforma per partita IVA del produttore. Le provvigioni di coordinamento vengono differenziate automaticamente.
- **Annulla Provvigioni**: porta le provvigioni selezionate in stato `Annullato`.
- **Excel**: esporta la lista raggruppata per produttore con totali.

**Filtri disponibili:**
- Stato (multiplo, default: Inserito + Sospeso)
- Coordinamento (Sì/No)
- Abbinato a fattura (Sì/No)
- Erogato (Sì/No)
- Stato Compenso
- Produttore (ricerca testuale)
- Istituto Finanziario (ricerca testuale)
- **Fino al mese**: filtra le provvigioni perfezionate entro il mese selezionato (default: mese precedente)
- **Mese erogazione**: filtra per mese di erogazione pratica
- **Trimestre erogazione**: filtra per trimestre (1°–4°)

**Raggruppamento:** di default per Produttore; disponibile anche per Stato.

---

### 1.2 Provvigioni Attive

Vista separata accessibile dal menu "Provvigioni Attive". Mostra le provvigioni attive (entrate da istituti), con gli stessi filtri della vista principale.

---

## 2. Proforma

### 2.1 Lista Proforma

Mostra tutti i proforma emessi verso produttori e istituti.

**Colonne:** Proforma (oggetto email), Stato, Compenso, Contributo, Anticipo, Data modifica, Produttore, Email, Delta.

**Stati:** `Inserito`, `Spedito`, `Pagato`, `Annullato`.

**Tipi:** `Agente`, `Istituto`, `Cliente`.

**Filtri:**
- Stato (default: Inserito)
- Tipo (default: Agente)
- Riconciliazione (Tutti / Riconciliati / Non riconciliati)
- Data Invio (range con query builder)

**Azioni di massa (toolbar):**
- **Invia email Proforma (al produttore)**: invia l'email con il dettaglio provvigionale al produttore reale.
- **Simulazione invio email (a se stessi)**: invia una copia a se stessi per verifica prima dell'invio reale.
- **Forza data invio email senza inviarla**: segna il proforma come inviato e aggiorna il montante anticipi del produttore, senza spedire l'email.

**Export Excel:** disponibile in header, raggruppato per proforma con totali di compenso, contributo, anticipo e delta.

---

### 2.2 Dettaglio Proforma (modifica)

Il form è organizzato in due tab:

**Tab Compensi:**
- **Anticipo**: importo di recupero mensile (0 = recupero totale del residuo); se negativo indica un'erogazione di anticipo.
- **Anticipo residuo**: montante totale degli anticipi ancora da rimborsare (sola lettura).
- **Totale provvigioni**: somma delle provvigioni abbinate (sola lettura).
- **Descrizione compenso**: testo descrittivo incluso nell'email.
- **Contributo fisso**: importo del contributo mensile configurato per il produttore.
- **Note aggiuntive**: testo libero aggiunto in coda all'email.

**Tab Email:**
- Oggetto e destinatario dell'email (modificabili manualmente).

**Sezione Provvigioni abbinate:** lista delle provvigioni collegate al proforma, con possibilità di rimuovere singole provvigioni (l'importo viene scalato dal compenso del proforma e la provvigione torna in stato `Inserito`).

---

## 3. Produttori (Anagrafiche)

### 3.1 Lista Produttori

Mostra tutti i produttori/agenti. Il badge nella navigazione segnala il numero di produttori **senza email** (esclusi i dipendenti).

**Colonne:** Nome, Anticipo residuo, Contributo, Enasarco, Coordinatore, P.IVA, Email, Dipendente, Regione, Città, Telefono.

**Filtri:**
- Email (Con / Senza)
- Enasarco (Con / Senza)
- Cestinati (TrashedFilter)

### 3.2 Scheda Produttore (modifica)

Il form è organizzato in tre tab:

**Tab Anagrafica:** Nome, Email, Tipo Enasarco (`no`, `monomandatario`, `plurimandatario`, `societa`), P.IVA, Codice Fiscale.

**Tab Anticipazioni e Contributi:**
- Descrizione e importo del contributo fisso mensile.
- Periodicità erogazione contributo (Mensile / Trimestrale / Semestrale / Annuale).
- Da quando erogare il contributo.
- Montante anticipo residuo da restituire.
- Rimborso mensile anticipo (0 = unica soluzione).
- Descrizione anticipo.

**Tab Dati Gestionali:** Coordinatore, Regione, Città, Data di nascita, Indirizzo, CAP, Provincia, Telefono, Conto COGE, Descrizione COGE, Denominazione in fattura elettronica, Codice, Flag Collaboratore/Dipendente.

---

## 4. Pratiche

### 4.1 Lista Pratiche

Mostra le pratiche importate da MediaFacile.

**Colonne:** Produttore, Cliente (cognome/nome), Banca, Tipo Prodotto, Stato Pratica, Data Erogazione, Data Inserimento, Codice Pratica.

**Filtri:**
- Stato pratica (multiplo, default: PERFEZIONATA + IN AMMORTAMENTO)
- Tipo prodotto (multiplo)
- Data erogazione (Presente / Assente)

**Export Excel** disponibile.

### 4.2 Dettaglio Pratica

Vista di sola lettura con le provvigioni collegate alla pratica (relation manager).

**Storno provvigioni per rivalsa:** se una provvigione già inviata all'agente viene stornata, il sistema genera automaticamente le rivalse sugli agenti coinvolti.

---

## 5. Contabilità

### 5.1 Primenote Provvigionali (Prospetto mensile)

Vista aggregata per mese con entrate, uscite, saldo, storni entrata e storni uscita.

- Cliccando su **Entrata** o **Uscita** si apre la lista provvigioni filtrata per quel mese.
- **Azione "Invia in Contabilità"**: invia la primanota del mese selezionato a Business Central tramite API (comando `coge:sync-monthly`).
- **Export Excel** con totali per colonna.

### 5.2 Configurazione Primenote (Settings → Primenote)

Tabella di configurazione dei conti COGE per fonte (es. `mediafacile`) e tipo (Entrata/Uscita), con conto dare, conto avere e relative descrizioni.

---

## 6. Fatturazione Passiva (Fatture Acquisto)

Sezione **Contabilita → Fatture passive**.

### 6.1 Lista Fatture Passive

**Colonne:** Data documento, Fornitore, Importo, Riconciliato, No Provvigioni (toggle), Annullata, N. documento, P.IVA.

**Filtri:** Data registrazione (range), Riconciliato, Associato a (Agente / Consulente / Non associato), Non legato a provvigioni, Annullate, Tipo documento.

**Raggruppamento** per Fornitore disponibile.

**Azioni su singola riga:**
- **Associa** (visibile solo se non ancora associata): permette di collegare la fattura a un agente o consulente esistente, oppure di crearne uno nuovo automaticamente a partire dai dati della fattura (nome e P.IVA).

**Azioni header:**
- **Importa Note Credito**: importa da file Excel (percorso `public/`) le note credito di acquisto.
- **Importa Fatture Acquisto Excel**: carica un file Excel con le fatture passive ricevute dalla contabilità.
- **Riconcilia con proforma**: avvia la riconciliazione automatica delle fatture passive con i proforma, abbinando per P.IVA del fornitore.
- **Ricava storico proforma**: ricostruisce i proforma storici degli agenti a partire dai dati MediaFacile già presenti.

**Azione bulk:**
- **Chiudi Selezionati**: marca le fatture selezionate come riconciliate.

**Relation manager:** mostra i proforma abbinati alla fattura dopo la registrazione.

---

## 7. Fatturazione Attiva (Fatture Vendita)

Sezione **Contabilita → Fatture attive**.

### 7.1 Lista Fatture Attive

**Colonne:** Data registrazione, Cliente, Importo, Riconciliato, No Provvigioni (toggle), P.IVA, Annullata, Tipo documento, Numero.

**Filtri:** Data registrazione (range), Riconciliato, Associato a (Istituto / Cliente / Non associato), Non legato a provvigioni, Annullate, Tipo documento.

**Azioni su singola riga:**
- **Associa**: collega la fattura a un istituto (Clienti con P.IVA) o a un cliente (senza P.IVA), con possibilità di creazione automatica.

**Azioni header:**
- **Importa Note Credito**: carica file Excel con note credito di vendita.
- **Importa Fatture Vendita Excel**: carica file Excel con le fatture attive emesse dalla contabilità.
- **Riconcilia con proforma**: riconciliazione automatica con i proforma degli istituti per P.IVA.
- **Ricava storico proforma**: ricostruisce i proforma storici degli istituti.

**Relation manager:** proforma abbinati alla fattura attiva.

---

## 8. ENASARCO

### 8.1 Trimestrali ENASARCO (Contabilita)

Vista dei contributi Enasarco per trimestre e produttore. Sola lettura, con dettaglio per trimestre.

### 8.2 Conguaglio ENASARCO e FIRR (Contabilita)

Vista annuale di conguaglio per produttore con:
- Montante imponibile, Contributo calcolato, Imposta, Credito produttore, Quota RACES, Versato (somma trimestri), **Conguaglio** (differenza da versare), FIRR, Anno di competenza, Tipo Enasarco.

**Filtro** per anno di competenza (default: anno precedente).

### 8.3 Configurazione Aliquote ENASARCO (Settings)

Tabella delle aliquote Enasarco per tipologia (monomandatario, plurimandatario, società) e anno di competenza.

---

## 9. Anagrafiche

### 9.1 Istituti / Mandanti (Clientis)

Anagrafica degli istituti finanziari con cui si lavora.

### 9.2 Clienti / Consulenti (Clients)

Anagrafica dei clienti e consulenti. Disponibile anche la vista **Consulenti** separata.

### 9.3 Aziende (Companies)

Gestione delle aziende del gruppo.

### 9.4 Tipi Cliente (ClientTypes)

Tabella di configurazione delle tipologie cliente.

---

## 10. Funzioni di Servizio

### 10.1 Import Pratiche da MediaFacile (API)

Comando schedulabile: `php artisan pratiche:import-api`

Opzioni:
- `--start-date=YYYY-MM-DD`: data inizio (default: 60 giorni fa)
- `--end-date=YYYY-MM-DD`: data fine (default: oggi)

Importa pratiche dall'API MediaFacile, creando automaticamente produttori (con P.IVA) e istituti se non esistono. Aggiorna le pratiche già presenti.

### 10.2 Import Provvigioni da MediaFacile (API)

Comando: `php artisan provvigioni:import-api`

Importa le provvigioni maturate dall'API MediaFacile, ricavando agenti con P.IVA (senza email), istituti (senza P.IVA) e nominativo cliente.

### 10.3 Sincronizzazione COGE mensile (Business Central)

Comando: `php artisan coge:sync-monthly --month=YYYY-MM`

Invia la primanota provvigionale del mese indicato a Business Central. Se non specificato, usa il mese precedente. Disponibile anche come azione diretta dalla vista **Primenote Provvigionali**.

### 10.4 Riconciliazione Fatture

Comandi disponibili:
- `php artisan invoices:match-proformas` — abbina proforma a fatture passive
- `php artisan invoices:match-purchase` — riconcilia fatture di acquisto
- `php artisan invoices:match-sales` — riconcilia fatture di vendita

---

## 11. Settings

| Voce | Descrizione |
|---|---|
| Primenote | Configurazione conti COGE per fonte e tipo movimento |
| Enasarco | Aliquote contributive per tipologia e anno |
| Utenti | Gestione accessi e ruoli |

---

## 12. Utenti

Gestione degli utenti dell'applicazione con creazione, modifica e visualizzazione del profilo. Accessibile da **Settings → Utenti**.
