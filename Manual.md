--------------------------------------------------------------
PROFORMA
 Gestione contabile Provvigioni Mediatori Creditizi
---

---

1. Gestione Provvigioni
   In questa sezione gestirai il ciclo di vita delle provvigioni

1.1 Provvigioni Passive (Uscite verso Produttori)
La dashboard principale mostra le provvigioni maturate dalla rete vendita.

Stati della Provvigione: Segui l'avanzamento da Inserito fino a Pagato o Fatturato.

Azione Rapida "Inverti Stato": Clicca sull'icona freccia per passare rapidamente tra Inserito e Sospeso. Usa questa funzione per escludere temporaneamente una provvigione dal prossimo proforma (ad esempio, per documentazione mancante).

Operazioni Massive:

Emetti Proforma: Seleziona le righe in stato Inserito. Il sistema le raggrupperà automaticamente per Partita IVA del produttore, separando i compensi standard da quelli di coordinamento.

Esportazione: Puoi scaricare i dati in Excel raggruppati per produttore per verifiche rapide off-line.

1.2 Provvigioni Attive (Entrate da Istituti)
Accessibile dal menu omonimo, questa vista ti permette di monitorare quanto dovuto dagli Istituti Finanziari alla tua società, utilizzando la stessa logica di filtri e ricerca della vista passiva.

Viene richiesto solo di selezionare le provvigioni del proforma fornito dalla banca e ( IMPORTATE ) la data del proforma.

Nella successiva fase di abbinamento delle fatture attive SOLO i proforma con data successiva alla data di registrazione della fattura potranno essere abbinate

Nota: Le provvigioni dei clienti vengono abbinate automaticamente alle fatture attive

2. Ciclo Proforma e Pagamenti
   Il Proforma è il documento intermedio che precede la fattura elettronica e serve a comunicare al produttore quanto può fatturare.

2.1 Gestione Proforma
Nella Lista Proforma, monitora lo stato dei documenti: Inserito, Spedito, Pagato.

Invia Email: Puoi procedere con l'invio reale al produttore o effettuare una Simulazione invio (riceverai tu l'email) per controllare il layout e i calcoli.

Forza data invio: Utile se l'invio avviene esternamente al sistema; aggiorna il montante anticipi senza generare email.

2.2 Dettaglio e Recupero Anticipi
Entrando in un proforma, troverai due schede fondamentali:

Tab Compensi: Qui gestisci il recupero degli Anticipi. Se il campo Anticipo è positivo, il sistema scala l'importo dal compenso del mese. Nota la presenza di un valore negativo, indica l'erogazionedi un nuovo anticipo.

Tab Provvigioni abbinate: Se decidi di rimuovere una provvigione dal proforma, l'importo totale si aggiornerà in tempo reale e la provvigione tornerà disponibile nella lista generale (Inserito).

Welcome bonus: in caso di welcome bonus andare in modifica sul proforma ed aggiungere l'importo nella voce Contributo.

3. Anagrafiche e Configurazioni Fiscali
   3.1 Produttori
   È fondamentale che ogni scheda riporti l'email per l'invio dei proforma e la tipologia Enasarco ( monomandatario / plurimandatario / societa / non soggetto ) per il corretto calcolo contributivo Enasarco

Il sistema segnala con un badge i produttori a cui manca l'indirizzo email, bloccando eventuali invii massivi errati.

Tab Anticipazioni: Configura la periodicità dell'eventuale contributo fisso e il piano di rientro del montante anticipi (es. restituzione in un'unica soluzione o rateizzata).

3.2 Istituti
Gestisci i dati degli istituti mandanti con eventuali Istituti fittizi ( utili in fase di istruttoria). Per gli istituti fittizi e' OBBLIGATORIO inserire la partita IVA su cui avverrà la fatturazione. L'assenza di P. IVA di un istituto attivo viene segnalata con un badge.

4. Pratiche e Storni (Rivalsa)
   Le pratiche vengono importate automaticamente da MediaFacile.

Storni: In caso di storno di una pratica (es. recesso del cliente), il sistema è in grado di generare automaticamente le rivalse sugli agenti coinvolti, creando una provvigione negativa che verrà compensata nel primo proforma utile.

5. Contabilità e Riconciliazione
   Questa è l'area operativa core per la chiusura mensile.

5.1 Primenote Provvigionali
Visualizza il riepilogo mensile (Entrate/Uscite/Saldo) delle provvigioni relative a pratiche erogate, al di la' del perfezionamento delle singole posizioni.

Sincronizzazione API: Una volta verificati i totali, clicca su "Invia in Contabilità" per trasmettere la primanota direttamente a Business Central.

5.2 Riconciliazione Fatture (Attive e Passive)
Il sistema importa le fatture elettroniche (tramite file Excel o integrazione).

Riconciliazione Automatica: Il comando "Riconcilia con proforma" abbina le fatture ai proforma emessi precedentemente alla data registrazione fattura basandosi sulla Partita IVA e sull'importo.

Associazione Manuale: Se una fattura non viene riconosciuta, puoi associarla manualmente a un agente / Istituto. Eventuali delta superiori ai 5 euro possono essere abbinati solo immettendo la giustificazione del delta

6. Gestione ENASARCO
   Il sistema automatizza il calcolo dei contributi in base alla tipologia definita in anagrafica (Monomandatario, Plurimandatario, Società).

Trimestrali: Visualizza il riepilogo dei contributi maturati nel trimestre per ogni produttore.

Conguaglio e FIRR: A fine anno (o su richiesta), consulta la vista di conguaglio che mette a confronto il calcolato con il versato, includendo la quota RACES e il calcolo del FIRR.

Aliquote: Le aliquote sono configurabili nei Settings per anno di competenza, garantendo la conformità anche in caso di variazioni normative.

7. Operazioni di Servizio (Import Automatici)
   Il sistema esegue dei compiti automatici di sincronizzazione. Sebbene siano schedulati, è bene conoscere le logiche:

Import API MediaFacile: Importa pratiche e provvigioni aggiornando gli stati in tempo reale.

Coge Sync: Sincronizza i conti dare/avere verso il software gestionale Business Central.

Nota per il Contabile: Se una provvigione non appare in lista, verifica che la data erogazione della pratica su MediaFacile rientri nei filtri impostati (solitamente "Fino al mese scorso").

8. Impostazioni (Settings)
   Sezione Cosa puoi fare
   Primenote Configura i conti del piano dei conti (Dare/Avere) per ogni tipologia di movimento.
   Enasarco Aggiorna i massimali e le aliquote annuali.
   Utenti Gestisci i permessi di accesso per i tuoi colleghi.
