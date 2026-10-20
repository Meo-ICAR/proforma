<?php

namespace App\Services;

use App\Models\Clienti;
use App\Models\Fornitore;
use App\Models\Proforma;
use App\Models\Provvigione;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Proforma fittizi per lo storico: le provvigioni che hanno già il numero e la data della fattura ma nessun proforma si raggruppano per
 * fattura, e ogni gruppo diventa un proforma `Pagato` che poi si abbina alla fattura di acquisto o di vendita come tutti gli altri
 * (InvoiceMatchingService).
 *
 *   Istituto → un proforma per (banca, n. fattura, data fattura): la banca è `istituto_finanziario` (`denominazione_riferimento` è il cliente
 *              finale della pratica) e il proforma è del cliente `clientis` con quel `nome`; si abbina a una fattura di vendita;
 *   Agente   → un proforma per (partita IVA dell'agente, n. fattura, data fattura), del fornitore, abbinato a una fattura di acquisto.
 *
 * Le provvigioni di tipo Cliente non passano dal proforma (vedi ProvvigioneInvoiceMatchingService). Rilanciare non duplica nulla: ogni proforma
 * porta in `legacy_id` la chiave del suo gruppo, e le provvigioni già collegate non si rielaborano.
 */
class StoricoProformaService
{
    /**
     * @param  array<int, string>  $tipi  Istituto, Agente (default: entrambi)
     * @return array{groups: int, created: int, reused: int, provvigioni_linked: int, skipped: array<string, int>, dry_run: bool}
     */
    public function generate(array $tipi = ['Istituto', 'Agente'], bool $dryRun = false): array
    {
        $stats = ['groups' => 0, 'created' => 0, 'reused' => 0, 'provvigioni_linked' => 0, 'skipped' => [], 'dry_run' => $dryRun];

        $groups = Provvigione::query()
            ->whereIn('tipo', $tipi)
            ->whereNull('proforma_id')
            ->where('annullato', false)
            ->whereNotNull('data_fattura')
            ->whereNotNull('n_fattura')
            ->whereRaw("trim(n_fattura) <> ''")
            ->orderBy('data_fattura')
            ->get()
            ->groupBy(fn (Provvigione $provvigione): string => $this->chiave($provvigione));

        foreach ($groups as $chiave => $provvigioni) {
            $stats['groups']++;

            $parte = $this->parte($provvigioni->first());
            if ($parte === null) {
                $motivo = $provvigioni->first()->tipo === 'Istituto' ? 'banca non presente tra i clienti' : 'agente non presente tra i fornitori';
                $stats['skipped'][$motivo] = ($stats['skipped'][$motivo] ?? 0) + $provvigioni->count();

                continue;
            }

            if ($dryRun) {
                $stats[Proforma::query()->where('legacy_id', $this->legacyId($chiave))->exists() ? 'reused' : 'created']++;
                $stats['provvigioni_linked'] += $provvigioni->count();

                continue;
            }

            DB::transaction(function () use ($chiave, $provvigioni, $parte, &$stats): void {
                $proforma = Proforma::query()->where('legacy_id', $this->legacyId($chiave))->first();

                if ($proforma === null) {
                    $proforma = $this->creaProforma($chiave, $provvigioni, $parte);
                    $stats['created']++;
                } else {
                    $stats['reused']++;
                }

                Provvigione::query()->whereIn('id', $provvigioni->pluck('id'))->update(['proforma_id' => $proforma->id]);
                $stats['provvigioni_linked'] += $provvigioni->count();
            });
        }

        return $stats;
    }

    /**
     * Riporta i proforma storici già creati alla banca che oggi risulta dalle regole (es. un cliente che nel frattempo non è più fittizio).
     *
     * @return int Proforma aggiornati
     */
    public function riallinea(bool $dryRun = false): int
    {
        $aggiornati = 0;

        Proforma::query()->where('legacy_id', 'like', 'S-%')->where('tipo', 'Istituto')->get()->each(function (Proforma $proforma) use ($dryRun, &$aggiornati): void {
            $provvigione = Provvigione::query()->where('proforma_id', $proforma->id)->first();
            $parte = $provvigione ? $this->parte($provvigione) : null;

            if ($parte === null || ((int) $proforma->fornitori_id === (int) $parte['id'] && $proforma->vat_number === $parte['piva'])) {
                return;
            }

            if (! $dryRun) {
                $proforma->update(['fornitori_id' => $parte['id'], 'vat_number' => $parte['piva']]);
            }
            $aggiornati++;
        });

        return $aggiornati;
    }

    private function creaProforma(string $chiave, Collection $provvigioni, array $parte): Proforma
    {
        $prima = $provvigioni->first();
        $totale = round((float) $provvigioni->sum('importo'), 2);
        $numero = trim((string) $prima->n_fattura);
        $data = $prima->data_fattura;

        $proforma = new Proforma([
            'tipo' => $prima->tipo,
            'stato' => 'Pagato',
            'fornitori_id' => $parte['id'],
            'vat_number' => $parte['piva'],
            'compenso' => $totale,
            'anticipo' => 0,
            'contributo' => 0,
            'sended_at' => $data->copy()->startOfDay(),
            'paid_at' => $data->copy()->startOfDay(),
            'emailsubject' => "Storico {$parte['name']} fattura n. {$numero} del {$data->format('d/m/Y')} totale ".number_format($totale, 2, ',', '.'),
            'annotation' => "Proforma fittizio generato dalle {$provvigioni->count()} provvigioni con fattura n. {$numero} del {$data->format('d/m/Y')}.",
        ]);
        $proforma->forceFill(['legacy_id' => $this->legacyId($chiave)])->save();

        return $proforma;
    }

    /** La fattura è identificata da parte, numero e data: lo stesso numero si ripete ogni anno e per ogni fornitore o banca. */
    private function chiave(Provvigione $provvigione): string
    {
        $parte = $provvigione->tipo === 'Istituto'
            ? $this->nome($provvigione->istituto_finanziario)
            : (filled($provvigione->piva) ? mb_strtoupper(trim($provvigione->piva)) : $this->nome($provvigione->denominazione_riferimento));

        return implode('|', [$provvigione->tipo, $parte, $this->nome($provvigione->n_fattura), $provvigione->data_fattura->toDateString()]);
    }

    /** `legacy_id` è lungo 36 caratteri: la chiave del gruppo vi entra come impronta. */
    private function legacyId(string $chiave): string
    {
        return 'S-'.md5($chiave);
    }

    /** @return array{id: int, name: string, piva: ?string}|null */
    private function parte(Provvigione $provvigione): ?array
    {
        if ($provvigione->tipo === 'Agente') {
            $fornitore = Fornitore::withoutGlobalScopes()
                ->when(filled($provvigione->piva), fn ($query) => $query->where('piva', trim($provvigione->piva)))
                ->when(! filled($provvigione->piva), fn ($query) => $query->where('name', $provvigione->denominazione_riferimento))
                ->orderByRaw('deleted_at is not null')
                ->orderBy('id')
                ->first();

            return $fornitore ? ['id' => $fornitore->id, 'name' => (string) $fornitore->name, 'piva' => $fornitore->piva] : null;
        }

        $cliente = $this->banca($provvigione->istituto_finanziario);

        return $cliente ? ['id' => $cliente->id, 'name' => (string) $cliente->name, 'piva' => $cliente->piva] : null;
    }

    /**
     * La banca è il cliente `clientis` il cui `nome` (come nelle fatture di vendita) corrisponde a `istituto_finanziario`, ignorando maiuscole,
     * punteggiatura e spazi («IBL BANCA SPA» = «IBL Banca S.P.A.»). Se nessun `nome` corrisponde si prova con `name`. Lo stesso soggetto può essere
     * in anagrafica più volte: vale il cliente non eliminato, poi non fittizio, poi con id più basso. Se è fittizio vale il cliente non fittizio con la
     * stessa partita IVA (`reale`).
     */
    private function banca(?string $nome): ?Clienti
    {
        $chiave = $this->alfanumerico($nome);
        if ($chiave === '') {
            return null;
        }

        foreach (['nome', 'name'] as $colonna) {
            $cliente = Clienti::withoutGlobalScopes()
                ->whereRaw("upper(regexp_replace({$colonna}, '[^A-Za-z0-9]', '')) = ?", [$chiave])
                ->orderByRaw('deleted_at is not null')
                ->orderBy('is_dummy')
                ->orderBy('id')
                ->first();

            if ($cliente !== null) {
                return $this->reale($cliente);
            }
        }

        return null;
    }

    /**
     * Un cliente fittizio (`is_dummy`) è un segnaposto: il soggetto vero è quello con la sua stessa partita IVA e `is_dummy` falso, e così
     * l'abbinamento vale anche per la ragione sociale. Se non c'è, resta il fittizio.
     */
    private function reale(Clienti $cliente): Clienti
    {
        if (! $cliente->is_dummy || ! filled($cliente->piva)) {
            return $cliente;
        }

        return Clienti::withoutGlobalScopes()
            ->where('piva', $cliente->piva)
            ->where('is_dummy', false)
            ->orderByRaw('deleted_at is not null')
            ->orderBy('id')
            ->first() ?? $cliente;
    }

    private function alfanumerico(?string $nome): string
    {
        return mb_strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', (string) $nome));
    }

    private function nome(?string $nome): string
    {
        return mb_strtoupper(trim((string) preg_replace('/\s+/u', ' ', (string) $nome)));
    }
}
