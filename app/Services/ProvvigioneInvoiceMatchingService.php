<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use App\Models\Provvigione;
use App\Models\SalesInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Abbinamento delle provvigioni di tipo Cliente alle fatture di vendita, senza passare per il proforma.
 *
 * Un proforma è fatto di più provvigioni di dettaglio e arriva alla fattura da solo:
 *   - provvigioni.tipo = Istituto: proforma dell'istituto (cliente `clientis`), fattura di vendita per partita IVA, cliente non fittizio
 *     (ProformaInvoiceMatchingService);
 *   - provvigioni.tipo = Agente: proforma del fornitore, fattura di acquisto per partita IVA (ProformaPurchaseInvoiceMatchingService);
 *   - provvigioni.tipo = Cliente: il cliente è una persona e non c'è un proforma. Dalla pratica si prende il codice fiscale
 *     (pratiches.codice_fiscale) e con questo si identifica il cliente (`clients.tax_code`); la fattura di vendita, che per le persone ha solo
 *     il nome, si riconosce per nome e importo uguali a quelli della pratica e della provvigione.
 *
 * Fattura emessa = provvigione `Pagato`: all'abbinamento la provvigione passa a quello stato.
 */
class ProvvigioneInvoiceMatchingService
{
    /** Tolleranza sull'importo tra provvigione e fattura, in euro. */
    private const TOLLERANZA = 1.0;

    /**
     * @return array{processed: int, matched: int, clients_created: int, clients_updated: int, errors: string[]}
     */
    public function matchClienteProvvigioniToSalesInvoices(): array
    {
        $stats = ['processed' => 0, 'matched' => 0, 'clients_created' => 0, 'clients_updated' => 0, 'errors' => []];

        // Le fatture a persone non hanno partita IVA: si indicizzano per nome, una volta sola.
        $fatture = SalesInvoice::query()
            ->libere()
            ->where(fn ($query) => $query->whereNull('vat_number')->orWhere('vat_number', ''))
            ->whereNotNull('amount')
            ->orderBy('registration_date')
            ->get()
            ->groupBy(fn (SalesInvoice $fattura): string => $this->nome($fattura->customer_name));

        $provvigioni = Provvigione::query()
            ->with('pratica')
            ->where('tipo', 'Cliente')
            ->where('annullato', false)
            ->whereNull('invoiceable_id')
            ->whereNotNull('importo')
            ->orderBy('data_inserimento_compenso')
            ->get();

        $usate = [];

        foreach ($provvigioni as $provvigione) {
            $stats['processed']++;

            try {
                $this->matchProvvigione($provvigione, $fatture, $usate, $stats);
            } catch (\Throwable $e) {
                Log::error("[ProvvigioneMatching] Errore sulla provvigione {$provvigione->id}: ".$e->getMessage());
                $stats['errors'][] = "Provvigione {$provvigione->id}: ".$e->getMessage();
            }
        }

        Log::info("[ProvvigioneMatching] Elaborate: {$stats['processed']}, abbinate: {$stats['matched']}");

        return $stats;
    }

    /**
     * @param  Collection<string, Collection<int, SalesInvoice>>  $fatture
     * @param  array<int, true>  $usate  Fatture già abbinate in questa esecuzione
     */
    private function matchProvvigione(Provvigione $provvigione, Collection $fatture, array &$usate, array &$stats): void
    {
        $pratica = $provvigione->pratica;
        $codiceFiscale = $this->codiceFiscale($pratica?->codice_fiscale);

        if ($pratica === null || $codiceFiscale === null) {
            return;
        }

        $candidate = $this->nomiPossibili($pratica->cognome_cliente, $pratica->nome_cliente)
            ->flatMap(fn (string $nome): Collection => $fatture->get($nome, collect()))
            ->reject(fn (SalesInvoice $fattura): bool => isset($usate[$fattura->id]))
            ->filter(fn (SalesInvoice $fattura): bool => abs((float) $fattura->amount - (float) $provvigione->importo) <= self::TOLLERANZA);

        // A parità di nome e importo si preferisce la fattura con il numero indicato sulla provvigione.
        $fattura = $candidate->first(fn (SalesInvoice $fattura): bool => $this->haNumero($fattura, $provvigione->n_fattura)) ?? $candidate->first();

        if ($fattura === null) {
            return;
        }

        DB::transaction(function () use ($provvigione, $fattura, $pratica, $codiceFiscale, &$stats): void {
            $client = $this->clientPerCodiceFiscale($fattura, $pratica, $codiceFiscale, $stats);

            $fattura->update(['invoiceable_type' => Client::class, 'invoiceable_id' => $client->id]);
            $provvigione->update([
                // Fattura emessa = provvigione pagata.
                'stato' => 'Pagato',
                'invoiceable_type' => SalesInvoice::class,
                'invoiceable_id' => $fattura->id,
                'invoice_number' => $provvigione->invoice_number ?: $fattura->number,
                'data_fattura' => $provvigione->data_fattura ?: $fattura->registration_date,
            ]);
        });

        // La fattura ha il suo abbinamento: non è più disponibile per un'altra provvigione.
        $usate[$fattura->id] = true;
        $stats['matched']++;
    }

    /**
     * Il cliente è quello con il codice fiscale della pratica. Se non c'è si usa quello già legato alla fattura (e gli si scrive il codice
     * fiscale), altrimenti se ne crea uno con i dati della pratica.
     */
    private function clientPerCodiceFiscale(SalesInvoice $fattura, $pratica, string $codiceFiscale, array &$stats): Client
    {
        $client = Client::query()->where('tax_code', $codiceFiscale)->first();
        if ($client !== null) {
            return $client;
        }

        if ($fattura->invoiceable_type === Client::class && ($client = Client::query()->find($fattura->invoiceable_id)) !== null) {
            $client->update(['tax_code' => $codiceFiscale]);
            $stats['clients_updated']++;

            return $client;
        }

        $stats['clients_created']++;

        return Client::query()->create([
            'company_id' => Company::query()->value('id'),
            'name' => $pratica->cognome_cliente ?: $fattura->customer_name,
            'first_name' => $pratica->nome_cliente,
            'tax_code' => $codiceFiscale,
            'is_person' => true,
            'is_client' => true,
        ]);
    }

    /** I due ordini possibili di cognome e nome: le fatture hanno «COGNOME NOME», ma a volte è invertito. */
    private function nomiPossibili(?string $cognome, ?string $nome): Collection
    {
        return collect([$this->nome("{$cognome} {$nome}"), $this->nome("{$nome} {$cognome}")])
            ->filter(fn (string $nome): bool => $nome !== '')
            ->unique()
            ->values();
    }

    private function nome(?string $nome): string
    {
        return mb_strtoupper(trim((string) preg_replace('/\s+/u', ' ', (string) $nome)));
    }

    private function codiceFiscale(?string $codiceFiscale): ?string
    {
        $codiceFiscale = mb_strtoupper(trim((string) $codiceFiscale));

        return $codiceFiscale === '' ? null : $codiceFiscale;
    }

    /** Il numero sulla provvigione è la parte finale di quello della fattura (153 per FVI25-00153). */
    private function haNumero(SalesInvoice $fattura, ?string $numero): bool
    {
        $numero = ltrim(trim((string) $numero), '0');

        return $numero !== '' && preg_match('/(?<![0-9])0*'.preg_quote($numero, '/').'$/', (string) $fattura->number) === 1;
    }

    /** @return array{unmatched_provvigioni: int, unmatched_invoices: int} */
    public function getUnmatchedStatistics(): array
    {
        return [
            'unmatched_provvigioni' => Provvigione::query()->where('tipo', 'Cliente')->where('annullato', false)->whereNull('invoiceable_id')->count(),
            'unmatched_invoices' => SalesInvoice::query()->libere()->where(fn ($query) => $query->whereNull('vat_number')->orWhere('vat_number', ''))->count(),
        ];
    }
}
