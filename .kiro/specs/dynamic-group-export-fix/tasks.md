# Implementation Plan

- [-] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - transform() non invocato dalla libreria
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: Scope the property to the concrete failing case — `groupBy('agente')` con 6 righe (2 agenti distinti, 3 righe ciascuno)
  - Istanziare `DynamicGroupExport` con `groupBy('agente')->sumColumns(['importo'])`
  - Eseguire l'export su una collection di 6 righe con 2 agenti distinti
  - Verificare che il file Excel contenga righe con prefisso "TOTALE" (una per gruppo)
  - Verificare che le righe siano raggruppate per valore della colonna `agente`
  - Il test assertions devono corrispondere all'Expected Behavior (Property 1 del design)
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found: il file contiene solo righe piatte senza "TOTALE", confermando che `transform()` non viene mai chiamato
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3_

- [ ] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Export piatto invariato quando groupBy è null
  - **IMPORTANT**: Follow observation-first methodology
  - Observe: con `$groupBy = null`, l'export produce righe piatte nell'ordine originale sul codice non corretto
  - Observe: il nome del file è nel formato `report_YYYY-MM-DD_HH-mm` sul codice non corretto
  - Observe: i filtri Filament (`->fromTable()`) continuano a essere applicati
  - Write property-based test: per tutti gli export con `$groupBy = null`, le righe prodotte sono identiche all'input originale (da Preservation Requirements nel design)
  - Write property-based test: per qualsiasi collection di dati con `$groupBy = null`, il file non contiene righe "TOTALE"
  - Verify tests pass on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [ ] 3. Fix per DynamicGroupExport — transform() non è un hook riconosciuto

  - [ ] 3.1 Implementare la fix: sovrascrivere registerEvents() con handler AfterSheet
    - Rimuovere il metodo `transform(Collection $rows)` (non è un hook reale di pxlrbt/filament-excel)
    - Rimuovere il metodo `transform_old(Collection $rows)` (metodo legacy non utilizzato)
    - Rimuovere il metodo `getAppliedFiltersHeader()` (metodo legacy non utilizzato)
    - Sovrascrivere `registerEvents()` per registrare un handler `AfterSheet`
    - Nell'handler `AfterSheet`: se `$groupBy` è null, non fare nulla (preservation)
    - Nell'handler `AfterSheet`: leggere tutte le righe dati dal foglio (dalla riga 2 in poi)
    - Raggruppare le righe per valore della colonna `$groupBy`
    - Pulire le righe dati dal foglio
    - Riscrivere le righe raggruppate con riga TOTALE al termine di ogni gruppo
    - Per ogni colonna in `$sumColumns`, calcolare la somma con pulizia di `€`, punti e virgola decimale
    - Aggiungere riga vuota separatrice tra i gruppi
    - Assicurarsi che tutti gli import necessari siano presenti (`AfterSheet`, `Worksheet`, ecc.)
    - _Bug_Condition: isBugCondition(export) dove export.groupBy IS NOT NULL_
    - _Expected_Behavior: righe raggruppate per groupBy con riga "TOTALE {GRUPPO}" e somme per sumColumns_
    - _Preservation: quando groupBy è null, il handler AfterSheet non modifica il foglio_
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4_

  - [ ] 3.2 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Raggruppamento e totali applicati all'export
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed — righe raggruppate con righe TOTALE presenti)
    - _Requirements: 2.1, 2.2, 2.3_

  - [ ] 3.3 Verify preservation tests still pass
    - **Property 2: Preservation** - Export piatto invariato quando groupBy è null
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions — export piatto invariato, filtri applicati, nome file corretto)
    - Confirm all tests still pass after fix (no regressions)

- [ ] 4. Checkpoint - Ensure all tests pass
  - Assicurarsi che tutti i test passino, chiedere all'utente se sorgono dubbi.
