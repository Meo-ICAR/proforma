# Bugfix Requirements Document

## Introduction

La classe `DynamicGroupExport` in `app/Filament/Exports/DynamicGroupExport.php` ha lo scopo di esportare dati in Excel con raggruppamento per colonna e righe di totale per gruppo. La logica è implementata nel metodo `transform(Collection $rows)`, ma questo metodo non è un hook riconosciuto da `pxlrbt/filament-excel`: la libreria non lo invoca mai durante il processo di export. Di conseguenza, i dati vengono esportati piatti, senza raggruppamento e senza righe di totale, rendendo la funzionalità completamente non operativa. Inoltre, il metodo `styles()` fa riferimento al tipo `Worksheet` senza il relativo `use` statement, causando un errore fatale se il metodo venisse raggiunto.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN viene avviato un export con `$groupBy` impostato su una colonna THEN il sistema esporta i dati senza alcun raggruppamento, come se `$groupBy` fosse `null`

1.2 WHEN viene avviato un export con `$sumColumns` impostato su una o più colonne THEN il sistema non produce righe di totale per nessun gruppo

1.3 WHEN il metodo `transform(Collection $rows)` è definito nella classe THEN il sistema non lo invoca mai, perché `pxlrbt/filament-excel` non riconosce `transform()` come hook del ciclo di export

1.4 WHEN il metodo `styles(Worksheet $sheet)` viene raggiunto THEN il sistema genera un errore fatale perché il tipo `Worksheet` non è importato con il relativo `use` statement

### Expected Behavior (Correct)

2.1 WHEN viene avviato un export con `$groupBy` impostato su una colonna THEN il sistema SHALL raggruppare le righe per il valore di quella colonna e presentarle in blocchi distinti nel file Excel

2.2 WHEN viene avviato un export con `$sumColumns` impostato su una o più colonne THEN il sistema SHALL inserire una riga di totale al termine di ogni gruppo, con la somma dei valori numerici per ciascuna colonna specificata

2.3 WHEN la logica di raggruppamento e somma deve essere eseguita THEN il sistema SHALL utilizzare un hook effettivamente riconosciuto da `pxlrbt/filament-excel` (es. sovrascrivere `query()` o usare le concern `WithCustomValueBinder` / `AfterSheet` event, oppure sovrascrivere il metodo corretto della libreria) per garantire che la trasformazione venga applicata ai dati prima della scrittura nel foglio

2.4 WHEN il metodo `styles()` viene invocato THEN il sistema SHALL avere il tipo `Worksheet` correttamente importato tramite `use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet` per evitare errori fatali

### Unchanged Behavior (Regression Prevention)

3.1 WHEN viene avviato un export senza impostare `$groupBy` THEN il sistema SHALL CONTINUE TO esportare tutte le righe piatte, nell'ordine originale, senza modifiche alla struttura

3.2 WHEN viene avviato un export con i filtri della tabella Filament attivi THEN il sistema SHALL CONTINUE TO applicare quei filtri ai dati esportati (comportamento garantito da `->fromTable()`)

3.3 WHEN viene avviato un export THEN il sistema SHALL CONTINUE TO generare il file con il nome nel formato `report_YYYY-MM-DD_HH-mm`

3.4 WHEN i dati contengono valori numerici formattati come stringhe con simbolo `€`, punti separatori delle migliaia e virgola decimale THEN il sistema SHALL CONTINUE TO convertirli correttamente in valori numerici per il calcolo delle somme

---

## Bug Condition (Pseudocode)

```pascal
FUNCTION isBugCondition(export)
  INPUT: export of type DynamicGroupExport
  OUTPUT: boolean

  // Il bug si manifesta ogni volta che groupBy è impostato,
  // perché transform() non viene mai chiamato dalla libreria
  RETURN export.groupBy IS NOT NULL
END FUNCTION
```

```pascal
// Property: Fix Checking
FOR ALL export WHERE isBugCondition(export) DO
  result ← runExport'(export)
  ASSERT result.rows ARE grouped BY export.groupBy
  ASSERT result.rows CONTAIN summary rows WITH sums FOR export.sumColumns
END FOR
```

```pascal
// Property: Preservation Checking
FOR ALL export WHERE NOT isBugCondition(export) DO
  ASSERT runExport(export) = runExport'(export)
END FOR
```
