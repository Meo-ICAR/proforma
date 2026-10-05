# Dynamic Group Export Fix - Bugfix Design

## Overview

La classe `DynamicGroupExport` tenta di applicare raggruppamento e righe di totale sovrascrivendo un metodo `transform(Collection $rows)` che non esiste come hook nella libreria `pxlrbt/filament-excel`. La libreria non invoca mai questo metodo, quindi i dati vengono esportati piatti. La fix consiste nel sostituire l'approccio con un meccanismo reale della libreria: sovrascrivere `registerEvents()` per registrare un handler `AfterSheet` che manipola il foglio Excel dopo la scrittura dei dati, oppure cambiare la sorgente dati da `FromQuery` a `FromCollection` per controllare direttamente le righe prodotte.

## Glossary

- **Bug_Condition (C)**: La condizione che attiva il bug — `$groupBy` è impostato su una colonna, ma `transform()` non viene mai invocato dalla libreria
- **Property (P)**: Il comportamento atteso — le righe devono essere raggruppate per colonna e ogni gruppo deve terminare con una riga di totale
- **Preservation**: Il comportamento esistente che non deve cambiare — export piatto quando `$groupBy` è null, applicazione dei filtri Filament, nome file nel formato `report_YYYY-MM-DD_HH-mm`
- **DynamicGroupExport**: La classe in `app/Filament/Exports/DynamicGroupExport.php` che estende `ExcelExport`
- **ExcelExport**: La classe base di `pxlrbt/filament-excel` che implementa `FromQuery`, `HasMapping`, `WithEvents`
- **registerEvents()**: Metodo di `ExcelExport` (e dell'interfaccia `WithEvents` di maatwebsite/excel) che restituisce un array di event handler — questo è un hook reale invocato dalla libreria
- **AfterSheet**: Evento di maatwebsite/excel lanciato dopo che tutte le righe sono state scritte nel foglio — permette di manipolare il `Worksheet` PhpSpreadsheet
- **FromCollection**: Interfaccia di maatwebsite/excel alternativa a `FromQuery` — permette di restituire una `Collection` di righe già trasformate tramite il metodo `collection()`

## Bug Details

### Bug Condition

Il bug si manifesta ogni volta che `$groupBy` è impostato su una colonna. Il metodo `transform(Collection $rows)` è definito nella classe ma non corrisponde ad alcun hook riconosciuto da `pxlrbt/filament-excel` né da `maatwebsite/excel`. La libreria non lo invoca mai durante il ciclo di export.

**Formal Specification:**
```
FUNCTION isBugCondition(export)
  INPUT: export of type DynamicGroupExport
  OUTPUT: boolean

  RETURN export.groupBy IS NOT NULL
END FUNCTION
```

### Examples

- `$export->groupBy('agente')` con 10 righe → atteso: righe raggruppate per agente con riga TOTALE per gruppo; effettivo: 10 righe piatte senza totali
- `$export->groupBy('prodotto')->sumColumns(['importo'])` → atteso: somma di `importo` per ogni gruppo; effettivo: nessuna riga di totale
- `$export->groupBy(null)` (default) → atteso: righe piatte nell'ordine originale; effettivo: righe piatte (comportamento corretto, non affetto dal bug)
- `$export->sumColumns(['importo'])` senza `groupBy` → atteso: righe piatte senza totali; effettivo: righe piatte (non affetto dal bug)

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Quando `$groupBy` è null, il sistema deve continuare a esportare tutte le righe piatte nell'ordine originale
- I filtri della tabella Filament devono continuare a essere applicati ai dati esportati (garantito da `->fromTable()`)
- Il nome del file deve continuare a essere generato nel formato `report_YYYY-MM-DD_HH-mm`
- I valori numerici formattati come stringhe con simbolo `€`, punti separatori e virgola decimale devono continuare a essere convertiti correttamente per il calcolo delle somme

**Scope:**
Tutti gli export avviati senza `$groupBy` impostato devono essere completamente non influenzati dalla fix. Questo include:
- Export standard senza raggruppamento
- Export con soli filtri Filament attivi
- Export con `$sumColumns` impostato ma senza `$groupBy`

## Hypothesized Root Cause

1. **Metodo inesistente come hook**: `transform(Collection $rows)` non è definito in nessuna interfaccia di `maatwebsite/excel` né in `ExcelExport`. La libreria non sa che deve chiamarlo. Il metodo `map($record)` (da `HasMapping`) è il vero hook per trasformare singole righe, ma opera su record individuali, non sull'intera collection.

2. **Architettura FromQuery incompatibile con trasformazioni bulk**: `ExcelExport` implementa `FromQuery`, che passa i record uno alla volta a `map()`. Non esiste un punto di intercettazione per riordinare o aggiungere righe sintetiche (totali) prima della scrittura nel foglio, a meno di usare `AfterSheet` o cambiare a `FromCollection`.

3. **Missing `use` statement per `Worksheet`**: Il file originale aveva già il `use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet` mancante nel metodo `styles()`, ma il file attuale lo ha già aggiunto. Questo problema è già risolto nel codice corrente.

4. **Approccio `AfterSheet` vs `FromCollection`**: L'approccio `AfterSheet` manipola il foglio dopo la scrittura (più complesso, richiede di lavorare con le API PhpSpreadsheet). L'approccio `FromCollection` è più pulito: si sovrascrive `query()` per raccogliere i dati, si applica la trasformazione, e si restituisce una `Collection` già strutturata.

## Correctness Properties

Property 1: Bug Condition - Raggruppamento e totali applicati all'export

_For any_ export dove `isBugCondition(export)` è true (cioè `$groupBy` è impostato), la funzione di export corretta SHALL produrre un file Excel in cui le righe sono raggruppate per il valore della colonna `$groupBy` e ogni gruppo è seguito da una riga di totale con la somma dei valori numerici per le colonne in `$sumColumns`.

**Validates: Requirements 2.1, 2.2, 2.3**

Property 2: Preservation - Export piatto invariato quando groupBy è null

_For any_ export dove `isBugCondition(export)` è false (cioè `$groupBy` è null), la funzione di export corretta SHALL produrre esattamente lo stesso risultato dell'export originale, preservando l'ordine delle righe, i filtri applicati e il nome del file.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4**

## Fix Implementation

### Approccio scelto: FromCollection con override di `query()`

L'approccio più pulito e testabile è sovrascrivere il metodo `query()` della classe base per intercettare i dati prima che vengano scritti nel foglio. Tuttavia, `ExcelExport` implementa `FromQuery` e non `FromCollection`. La soluzione corretta è implementare `WithEvents` sovrascrivendo `registerEvents()` per aggiungere un handler `AfterSheet` che manipola il foglio dopo la scrittura, oppure — più semplicemente — cambiare la classe per implementare anche `FromCollection` e sovrascrivere il metodo `collection()`.

**Approccio raccomandato: sovrascrivere `registerEvents()` con `AfterSheet`**

Questo approccio è compatibile con l'architettura esistente (`FromQuery` rimane invariato, i filtri Filament continuano a funzionare) e usa un hook reale della libreria.

### Changes Required

**File**: `app/Filament/Exports/DynamicGroupExport.php`

**Specific Changes**:

1. **Rimuovere il metodo `transform(Collection $rows)`**: Non è un hook reale, va eliminato per evitare confusione.

2. **Rimuovere il metodo `transform_old(Collection $rows)`**: Metodo legacy non utilizzato, va eliminato.

3. **Rimuovere il metodo `getAppliedFiltersHeader()`**: Metodo legacy non utilizzato, va eliminato.

4. **Sovrascrivere `registerEvents()`**: Registrare un handler per `AfterSheet` che, dopo la scrittura dei dati, legge le righe dal foglio, applica il raggruppamento e i totali, e riscrive il foglio con la struttura corretta.

5. **Aggiungere import necessari**: Assicurarsi che `AfterSheet`, `Worksheet` e le classi PhpSpreadsheet necessarie siano importate.

**Pseudocodice della logica in AfterSheet:**
```
ON AfterSheet event:
  IF groupBy IS NULL THEN RETURN  // preservation: nessuna modifica

  sheet ← event.sheet.getDelegate()
  rows ← leggi tutte le righe dal foglio (dalla riga 2 in poi, riga 1 = intestazioni)

  groups ← raggruppa rows per valore della colonna groupBy

  pulisci il foglio (rimuovi tutte le righe dati)

  currentRow ← 2
  FOR EACH group IN groups DO
    FOR EACH row IN group DO
      scrivi row nel foglio alla riga currentRow
      currentRow++
    END FOR

    summaryRow ← riga vuota
    summaryRow[groupBy] ← 'TOTALE ' + toUpperCase(groupName)
    FOR EACH col IN sumColumns DO
      summaryRow[col] ← somma dei valori numerici del gruppo per col
    END FOR
    scrivi summaryRow nel foglio alla riga currentRow
    currentRow++

    scrivi riga vuota alla riga currentRow  // separatore tra gruppi
    currentRow++
  END FOR
```

## Testing Strategy

### Validation Approach

La strategia di test segue due fasi: prima si verifica che il bug esista sul codice non corretto (exploratory), poi si verifica che la fix funzioni (fix checking) e che il comportamento esistente sia preservato (preservation checking).

### Exploratory Bug Condition Checking

**Goal**: Dimostrare che `transform()` non viene mai invocato dalla libreria sul codice attuale, confermando la root cause.

**Test Plan**: Scrivere un test che istanzia `DynamicGroupExport` con `$groupBy` impostato, esegue l'export su una collection di dati di test, e verifica che le righe nel file prodotto NON siano raggruppate. Questo test deve fallire sul codice attuale (dimostrando il bug) e passare dopo la fix.

**Test Cases**:
1. **Export con groupBy impostato (unfixed)**: Istanziare l'export con `groupBy('agente')`, eseguire su 6 righe con 2 agenti distinti, verificare che il file contenga righe di totale — fallirà sul codice non corretto
2. **Export con sumColumns (unfixed)**: Istanziare l'export con `groupBy('agente')->sumColumns(['importo'])`, verificare che le somme siano presenti — fallirà sul codice non corretto
3. **Verifica che transform() non venga chiamato**: Aggiungere un contatore nel metodo `transform()` e verificare che rimanga a 0 dopo l'export — passerà sul codice non corretto (confermando il bug)

**Expected Counterexamples**:
- Il file Excel prodotto contiene solo righe piatte senza raggruppamento
- Nessuna riga con prefisso "TOTALE" è presente nel file

### Fix Checking

**Goal**: Verificare che per tutti gli export dove `isBugCondition` è true, la funzione corretta produca il comportamento atteso.

**Pseudocode:**
```
FOR ALL export WHERE isBugCondition(export) DO
  result := runExport_fixed(export)
  ASSERT result.rows ARE grouped BY export.groupBy
  ASSERT result.rows CONTAIN summary rows WITH sums FOR export.sumColumns
END FOR
```

### Preservation Checking

**Goal**: Verificare che per tutti gli export dove `isBugCondition` è false, la funzione corretta produca lo stesso risultato dell'originale.

**Pseudocode:**
```
FOR ALL export WHERE NOT isBugCondition(export) DO
  ASSERT runExport_original(export) = runExport_fixed(export)
END FOR
```

**Testing Approach**: I test di preservazione verificano che:
- Export senza `$groupBy` producano righe piatte identiche
- I filtri Filament continuino a essere applicati
- Il nome del file rimanga nel formato corretto

**Test Cases**:
1. **Export piatto senza groupBy**: Verificare che con `$groupBy = null` le righe siano identiche prima e dopo la fix
2. **Nome file preservato**: Verificare che il filename sia nel formato `report_YYYY-MM-DD_HH-mm`
3. **Conversione valori numerici**: Verificare che stringhe con `€` e virgola decimale siano convertite correttamente nelle somme

### Unit Tests

- Test del raggruppamento: dati con 3 gruppi distinti → 3 blocchi + 3 righe TOTALE
- Test delle somme: valori numerici e stringhe con `€` → somme corrette
- Test edge case: `$groupBy` impostato ma tutti i record nello stesso gruppo → 1 blocco + 1 riga TOTALE
- Test edge case: `$sumColumns` vuoto → righe TOTALE con solo l'etichetta, nessuna somma
- Test preservation: `$groupBy = null` → righe piatte invariate

### Property-Based Tests

- Generare collection di record con N gruppi casuali → verificare che il file contenga esattamente N righe TOTALE
- Generare valori numerici casuali per `$sumColumns` → verificare che le somme nelle righe TOTALE siano corrette
- Generare export senza `$groupBy` con dati casuali → verificare che le righe siano identiche all'input

### Integration Tests

- Test end-to-end con una tabella Filament reale e filtri attivi → verificare che i filtri siano applicati e il raggruppamento funzioni
- Test con export in coda (queued) → verificare che il raggruppamento funzioni anche in modalità asincrona
- Test con più colonne di somma → verificare che tutte le colonne specificate abbiano i totali corretti
