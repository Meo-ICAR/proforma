# Graph Report - app  (2026-10-05)

## Corpus Check
- 271 files · ~57,573 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1710 nodes · 4146 edges · 85 communities (59 shown, 26 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 27 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- Tabelle Clienti/Consulenti
- Tabelle Fornitori/Clienti
- API Check Status
- Form Coges/ClientType
- Company Resource
- Import Provvigioni API
- Modelli Provvigioni
- Tabella Proformas
- Dashboard e Navigazione
- Pagine Lista
- Modello Clienti
- Pagine Edit
- Export Dinamico Excel
- Pratica Import/Export
- Tabella Provvigioni
- Check Clienti/Fornitori
- Panel Provider Filament
- Import Invoiceins
- Form Client
- Tabella Pratiche
- Import Fatture Vendita
- Form Clienti
- Prima Nota Pages
- Form/Infolist Fornitore
- Match Proforma-Fatture
- Import Fatture Acquisto
- Quick Excel Export
- Modello Proforma
- Tabella Attive
- Assistente Manuale AI
- Piani e Permessi
- Fornitore Resource
- View Fornitore
- Form Company
- Assistente AI Page
- Pagine Create
- Pratiche Stato Pages
- Fatture Vendita Tabelle
- Piani e Helpers
- Mail Escalation
- Relazioni Employee/Client
- Coges Resource
- Provvigioni Pages
- Sales Invoice Resource
- Prima Nota Entry Model
- Calcolo Vcoge/Import Daily
- Sync Coge Mensile
- Form Enasarco/Firr
- Modello Client
- Form Pratica
- Sales Invoice Import
- Fattura/Company Model
- Scoped MySQL Tools
- Import Note Credito
- Calcolo Venasarco
- Modello User
- Clienti Resource
- ClientType Resource
- Firr Resource
- Modelli Coges/Employee
- Purchase Invoice Resource
- Sync Prima Nota BC
- View Pratica
- Provvigioni Stato
- Match Proforma Acquisti
- UserRole
- Enasarco Resource
- Fattura Resource
- AppServiceProvider
- Form Prima Nota
- InvoiceIn Resource
- Pratiche Stato Table
- Plan Access Models
- Venasarco Trimestre
- Genera Prima Nota
- Import Pratiche Parsing
- Consulenti Pages
- Match Sales Invoices
- Fattura Pages
- Gemini Embeddings
- Istruzioni Widget

## God Nodes (most connected - your core abstractions)
1. `HasPlanAccess` - 59 edges
2. `Proforma` - 47 edges
3. `Fornitore` - 46 edges
4. `Client` - 43 edges
5. `Provvigione` - 41 edges
6. `PurchaseInvoice` - 40 edges
7. `SalesInvoice` - 39 edges
8. `DynamicGroupExport` - 38 edges
9. `Clienti` - 32 edges
10. `Company` - 28 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --references--> `StatusCheckCommand`  [EXTRACTED]
  Http/Controllers/Api/CheckStatusApiController.php → Console/Commands/StatusCheckCommand.php
- `{closure#2}()` --references--> `Proforma`  [EXTRACTED]
  Filament/Resources/PurchaseInvoices/RelationManagers/ProformasAfterRegistrationRelationManager.php → Models/Proforma.php
- `{closure#2}()` --references--> `Proforma`  [EXTRACTED]
  Filament/Resources/SalesInvoices/RelationManagers/ProformasAfterRegistrationRelationManager.php → Models/Proforma.php
- `{closure#1}()` --references_constant--> `PurchaseInvoice`  [EXTRACTED]
  Services/ProformaPurchaseInvoiceMatchingService.php → Models/PurchaseInvoice.php
- `{closure#1}()` --references_constant--> `SalesInvoice`  [EXTRACTED]
  Services/ProformaInvoiceMatchingService.php → Models/SalesInvoice.php

## Import Cycles
- None detected.

## Communities (85 total, 26 thin omitted)

### Community 0 - "Tabelle Clienti/Consulenti"
Cohesion: 0.06
Nodes (10): ClientsTable, {closure#8}(), ConsulentiTable, ClientTypesTable, EnasarcosTable, {closure#2}(), FirrsTable, InvoiceInsTable (+2 more)

### Community 1 - "Tabelle Fornitori/Clienti"
Cohesion: 0.05
Nodes (41): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#1}(), {closure#10}(), {closure#11}() (+33 more)

### Community 2 - "API Check Status"
Cohesion: 0.06
Nodes (14): CheckStatusApiController, {closure#1}(), CommandDispatchApiController, LatestModelRecordApiController, ModelFieldsApiController, ModelFieldValueApiController, UserLookupApiController, BpmBridgeController (+6 more)

### Community 3 - "Form Coges/ClientType"
Cohesion: 0.05
Nodes (14): ClientTypeForm, CogesForm, CogesInfolist, CompanyInfolist, EnasarcoInfolist, PraticheStatoForm, PraticheStatoInfolist, ProvvigioneForm (+6 more)

### Community 4 - "Company Resource"
Cohesion: 0.06
Nodes (16): CompanyResource, CreateCompany, ListCompanies, ViewCompany, CompaniesTable, ViewEnasarco, ViewProvvigioniStato, CreateUser (+8 more)

### Community 5 - "Import Provvigioni API"
Cohesion: 0.06
Nodes (11): ImportProvvigioniFromApi, {closure#2}(), {closure#3}(), {closure#4}(), {closure#6}(), ProvvigioniRelationManager, ProvvigioniRelationManager, ProformasAfterRegistrationRelationManager (+3 more)

### Community 6 - "Modelli Provvigioni"
Cohesion: 0.07
Nodes (8): {closure#9}(), {closure#10}(), {closure#11}(), {closure#9}(), Company, Fornitore, {closure#1}(), {closure#1}()

### Community 7 - "Tabella Proformas"
Cohesion: 0.06
Nodes (23): {closure#28}(), {closure#2}(), {closure#4}(), {closure#6}(), {closure#8}(), {closure#3}(), {closure#5}(), {closure#4}() (+15 more)

### Community 8 - "Dashboard e Navigazione"
Cohesion: 0.10
Nodes (10): Dashboard, ClientResource, CreatePrimaNotaConfig, PrimaNotaConfigResource, CreateProforma, VcogeForm, VcogeResource, EditVenasarcotot (+2 more)

### Community 9 - "Pagine Lista"
Cohesion: 0.10
Nodes (6): ListClients, ListPraticas, ListPrimaNotaConfigs, ListPurchaseInvoices, ListUsers, ListVcoges

### Community 10 - "Modello Clienti"
Cohesion: 0.09
Nodes (8): {closure#14}(), {closure#10}(), {closure#13}(), {closure#7}(), Clienti, SalesInvoice, ProformaInvoiceMatchingService, SalesInvoiceMatchingService

### Community 11 - "Pagine Edit"
Cohesion: 0.11
Nodes (5): EditCoges, EditCompany, EditPrimaNotaConfig, EditVcoge, EditVenasarcoTrimestre

### Community 12 - "Export Dinamico Excel"
Cohesion: 0.07
Nodes (7): {closure#1}(), DynamicGroupExport, ClientisTable, FornitoresTable, VcogesTable, VenasarcototsTable, VenasarcoTrimestresTable

### Community 13 - "Pratica Import/Export"
Cohesion: 0.09
Nodes (5): {closure#1}(), EditPratica, ViewProvvigione, ListVenasarcotots, VenasarcototResource

### Community 14 - "Tabella Provvigioni"
Cohesion: 0.07
Nodes (14): {closure#1}(), {closure#11}(), {closure#13}(), {closure#15}(), {closure#16}(), {closure#17}(), {closure#18}(), {closure#19}() (+6 more)

### Community 15 - "Check Clienti/Fornitori"
Cohesion: 0.12
Nodes (11): CheckClientiWithoutPiva, CheckFornitoriWithoutEmail, CheckProformasUnpaid, CheckStaleSalesInvoices, StatusCheckCommand, Severity, Alert, Ok (+3 more)

### Community 17 - "Import Invoiceins"
Cohesion: 0.10
Nodes (3): ImportInvoiceins, InvoiceinsImport, InvoicesImport

### Community 18 - "Form Client"
Cohesion: 0.11
Nodes (20): ClientForm, {closure#1}(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#13}(), {closure#14}(), {closure#2}() (+12 more)

### Community 19 - "Tabella Pratiche"
Cohesion: 0.10
Nodes (9): {closure#1}(), ProvvigioneResource, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#1}(), {closure#2}() (+1 more)

### Community 20 - "Import Fatture Vendita"
Cohesion: 0.15
Nodes (3): {closure#19}(), Client, SalesInvoiceCreditNoteImportService

### Community 21 - "Form Clienti"
Cohesion: 0.10
Nodes (4): ClientiForm, InvoiceInForm, PurchaseInvoiceForm, SalesInvoiceForm

### Community 22 - "Prima Nota Pages"
Cohesion: 0.10
Nodes (11): {closure#6}(), {closure#9}(), {closure#4}(), {closure#1}(), {closure#12}(), CreatePrimaNotaEntry, EditPrimaNotaEntry, ListPrimaNotaEntries (+3 more)

### Community 23 - "Form/Infolist Fornitore"
Cohesion: 0.10
Nodes (4): FornitoreForm, FornitoreInfoList, ProformaForm, ProvvigioneInfoList

### Community 24 - "Match Proforma-Fatture"
Cohesion: 0.17
Nodes (3): PurchaseInvoice, ProformaPurchaseInvoiceMatchingService, PurchaseInvoiceMatchingService

### Community 26 - "Quick Excel Export"
Cohesion: 0.09
Nodes (6): QuickExcelExportAction, ProformasTable, {closure#2}(), {closure#6}(), PurchaseInvoicesTable, Compenso

### Community 27 - "Modello Proforma"
Cohesion: 0.09
Nodes (5): {closure#4}(), {closure#5}(), {closure#1}(), {closure#2}(), Proforma

### Community 28 - "Tabella Attive"
Cohesion: 0.10
Nodes (7): {closure#1}(), {closure#11}(), {closure#13}(), {closure#15}(), {closure#17}(), {closure#8}(), {closure#9}()

### Community 30 - "Piani e Permessi"
Cohesion: 0.15
Nodes (3): HasPlanAccess, AddressType, Enasarco

### Community 31 - "Fornitore Resource"
Cohesion: 0.14
Nodes (5): FornitoreResource, CreateFornitore, EditFornitore, ListFornitores, ViewFornitore

### Community 32 - "View Fornitore"
Cohesion: 0.12
Nodes (6): {closure#1}(), {closure#3}(), EditProforma, ListProformas, ProformaResource, ProformaEditSchema

### Community 33 - "Form Company"
Cohesion: 0.15
Nodes (3): CompanyForm, FatturaForm, UserForm

### Community 34 - "Assistente AI Page"
Cohesion: 0.15
Nodes (3): AssistenteAi, {closure#2}(), Manuale

### Community 35 - "Pagine Create"
Cohesion: 0.18
Nodes (8): CreateClienti, CreateClient, CreateInvoiceIn, CreatePratica, CreateProvvigione, CreateVcoge, CreateVenasarcotot, CreateVenasarcoTrimestre

### Community 36 - "Pratiche Stato Pages"
Cohesion: 0.18
Nodes (6): CreatePraticheStato, EditPraticheStato, ListPraticheStatos, ViewPraticheStato, PraticheStatoResource, PraticheStatosTable

### Community 37 - "Fatture Vendita Tabelle"
Cohesion: 0.12
Nodes (3): {closure#2}(), {closure#5}(), SalesInvoicesTable

### Community 38 - "Piani e Helpers"
Cohesion: 0.17
Nodes (8): PlanType, Base, Full, Medium, checkPiano(), resolvePianoAccess(), resolveUserEmployeeTypeIds(), Resource

### Community 41 - "Coges Resource"
Cohesion: 0.21
Nodes (5): CogesResource, CreateCoges, ListCoges, ViewCoges, CogesTable

### Community 42 - "Provvigioni Pages"
Cohesion: 0.14
Nodes (4): EditProvvigione, ListProvvigiones, ListProvvigioniAttive, AttiveTable

### Community 43 - "Sales Invoice Resource"
Cohesion: 0.19
Nodes (4): CreateSalesInvoice, EditSalesInvoice, ListSalesInvoices, SalesInvoiceResource

### Community 45 - "Calcolo Vcoge/Import Daily"
Cohesion: 0.22
Nodes (4): CalculateVcoge, ImportDailyData, MatchProformasToInvoices, SyncResourcesCommand

### Community 46 - "Sync Coge Mensile"
Cohesion: 0.17
Nodes (4): SyncCogeMonthly, {closure#5}(), {closure#6}(), Vcoge

### Community 47 - "Form Enasarco/Firr"
Cohesion: 0.15
Nodes (3): EnasarcoForm, FirrForm, VenasarcototForm

### Community 48 - "Modello Client"
Cohesion: 0.18
Nodes (3): {closure#3}(), ClientType, PrimaNotaConfig

### Community 49 - "Form Pratica"
Cohesion: 0.22
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), PraticaForm, Pratica

### Community 54 - "Calcolo Venasarco"
Cohesion: 0.20
Nodes (4): CalculateVenasarcotot, {closure#6}(), Firr, Venasarcotot

### Community 56 - "Clienti Resource"
Cohesion: 0.17
Nodes (3): ClientiResource, EditClienti, ListClientis

### Community 57 - "ClientType Resource"
Cohesion: 0.26
Nodes (4): ClientTypeResource, CreateClientType, EditClientType, ListClientTypes

### Community 58 - "Firr Resource"
Cohesion: 0.26
Nodes (4): FirrResource, CreateFirr, EditFirr, ListFirrs

### Community 59 - "Modelli Coges/Employee"
Cohesion: 0.21
Nodes (6): {closure#1}(), {closure#2}(), Coges, {closure#1}(), InvoiceIn, ProvvigioniStato

### Community 60 - "Purchase Invoice Resource"
Cohesion: 0.21
Nodes (3): CreatePurchaseInvoice, EditPurchaseInvoice, PurchaseInvoiceResource

### Community 63 - "View Pratica"
Cohesion: 0.22
Nodes (6): ViewPratica, PraticaResource, PraticasTable, {closure#1}(), {closure#9}(), {closure#10}()

### Community 64 - "Provvigioni Stato"
Cohesion: 0.27
Nodes (4): CreateProvvigioniStato, EditProvvigioniStato, ListProvvigioniStatos, ProvvigioniStatoResource

### Community 67 - "UserRole"
Cohesion: 0.33
Nodes (7): UserRole, ADMIN, INSPECTOR, QUALITY, SOS, SUPER_ADMIN, USER

### Community 68 - "Enasarco Resource"
Cohesion: 0.29
Nodes (4): EnasarcoResource, CreateEnasarco, EditEnasarco, ListEnasarcos

### Community 69 - "Fattura Resource"
Cohesion: 0.24
Nodes (3): FatturaResource, CreateFattura, FatturasTable

### Community 71 - "Form Prima Nota"
Cohesion: 0.28
Nodes (6): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}()

### Community 72 - "InvoiceIn Resource"
Cohesion: 0.28
Nodes (3): InvoiceInResource, EditInvoiceIn, ListInvoiceIns

### Community 73 - "Pratiche Stato Table"
Cohesion: 0.31
Nodes (5): {closure#1}(), {closure#2}(), {closure#3}(), PraticheStato, TipoProdotto

### Community 75 - "Venasarco Trimestre"
Cohesion: 0.29
Nodes (3): CalculateVenasarcoTrimestre, {closure#1}(), VenasarcoTrimestre

## Knowledge Gaps
- **4 isolated node(s):** `Base`, `Medium`, `Full`, `SOS`
  These have ≤1 connection - possible missing edges. (Counts symbols only; 421 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **26 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Proforma` connect `Modello Proforma` to `View Fornitore`, `Tabelle Fornitori/Clienti`, `Modelli Provvigioni`, `Tabella Proformas`, `Dashboard e Navigazione`, `Plan Access Models`, `Modello Clienti`, `Tabella Provvigioni`, `Check Clienti/Fornitori`, `Import Invoiceins`, `Form Client`, `Fattura/Company Model`, `Match Proforma-Fatture`, `Tabella Attive`, `Pratica e Relazioni`, `Piani e Permessi`?**
  _High betweenness centrality (0.064) - this node is a cross-community bridge._
- **What connects `Base`, `Medium`, `Full` to the rest of the system?**
  _4 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Tabelle Clienti/Consulenti` be split into smaller, more focused modules?**
  _Cohesion score 0.06490384615384616 - nodes in this community are weakly interconnected._
- **Why does `Client` connect `Import Fatture Vendita` to `Tabelle Clienti/Consulenti`, `Tabelle Fornitori/Clienti`, `Import Pratiche API`, `Import Provvigioni API`, `Modelli Provvigioni`, `Fatture Vendita Tabelle`, `Dashboard e Navigazione`, `Relazioni Employee/Client`, `Modello Clienti`, `Modello Client`, `Form Clienti`, `Match Proforma-Fatture`, `Import Fatture Acquisto`, `Quick Excel Export`, `Pratica e Relazioni`, `Piani e Permessi`?**
  _High betweenness centrality (0.053) - this node is a cross-community bridge._
- **Should `Tabelle Fornitori/Clienti` be split into smaller, more focused modules?**
  _Cohesion score 0.05384615384615385 - nodes in this community are weakly interconnected._
- **Why does `Fornitore` connect `Modelli Provvigioni` to `Tabelle Fornitori/Clienti`, `API Check Status`, `Import Provvigioni API`, `Check Clienti/Fornitori`, `Import Invoiceins`, `Form Client`, `Prima Nota Pages`, `Form/Infolist Fornitore`, `Match Proforma-Fatture`, `Import Fatture Acquisto`, `Quick Excel Export`, `Piani e Permessi`, `Fornitore Resource`, `Fattura/Company Model`, `Modelli Coges/Employee`, `Pratica e Relazioni`, `Import Pratiche API`, `Form Prima Nota`, `Plan Access Models`, `Import Pratiche Parsing`?**
  _High betweenness centrality (0.052) - this node is a cross-community bridge._
- **Should `API Check Status` be split into smaller, more focused modules?**
  _Cohesion score 0.061955965181771634 - nodes in this community are weakly interconnected._