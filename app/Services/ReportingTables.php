<?php

namespace App\Services;

use App\Models\Firr;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Ricalcolo delle tabelle di riepilogo COGE ed ENASARCO senza svuotarle: le righe esistenti si aggiornano sulla loro chiave,
 * quelle nuove si inseriscono e si eliminano solo le righe che il calcolo non produce più. Tutto in una transazione.
 */
class ReportingTables
{
    private const CHUNK = 500;

    /**
     * Totali mensili vcoge (entrate, uscite e storni per mese).
     *
     * @return array{inserted_or_updated: int, deleted: int}
     */
    public static function refreshVcoge(): array
    {
        $source = DB::query()
            ->fromSub(ReportingQueries::coge(), 'c')
            ->leftJoinSub(ReportingQueries::cogeStorno(), 's', 's.mese', '=', 'c.mese')
            ->select('c.mese', 'c.entrata', 'c.uscita')
            ->selectRaw('COALESCE(s.storno_entrata, 0) as storno_entrata')
            ->selectRaw('COALESCE(s.storno_uscita, 0) as storno_uscita');

        return self::refresh('vcoge', $source, ['mese']);
    }

    /**
     * Contributi ENASARCO per produttore, anno e trimestre (venasarcotrimestre).
     *
     * @return array{inserted_or_updated: int, deleted: int}
     */
    public static function refreshEnasarcoTrimestre(): array
    {
        return self::refresh('venasarcotrimestre', ReportingQueries::enasarcoTrimestre(), ['competenza', 'Trimestre', 'produttore', 'enasarco']);
    }

    /**
     * Totali annui ENASARCO e FIRR per produttore (venasarcotot). Il FIRR si calcola sugli scaglioni di `firrs`.
     *
     * @return array{inserted_or_updated: int, deleted: int}
     */
    public static function refreshEnasarcoTotali(): array
    {
        return self::refresh(
            'venasarcotot',
            ReportingQueries::enasarcoTotali(),
            ['competenza', 'produttore', 'enasarco'],
            function (array $row): array {
                $row['firr'] = Firr::calculateContributo((float) $row['montante'], (string) $row['enasarco'], (int) $row['competenza']);

                return $row;
            },
        );
    }

    /**
     * @param  list<string>  $keys  colonne della chiave unica della tabella
     * @param  (callable(array<string, mixed>): array<string, mixed>)|null  $transform  completa ogni riga prima di scriverla
     * @return array{inserted_or_updated: int, deleted: int}
     */
    private static function refresh(string $table, Builder $source, array $keys, ?callable $transform = null): array
    {
        return DB::transaction(function () use ($table, $source, $keys, $transform): array {
            $rows = $source->get()->map(fn (object $row): array => (array) $row)->all();

            if ($transform !== null) {
                $rows = array_map($transform, $rows);
            }

            // Una chiave nulla non è confrontabile dall'indice unico e produrrebbe un duplicato a ogni ricalcolo.
            $rows = array_values(array_filter($rows, fn (array $row): bool => collect($keys)->every(fn (string $key): bool => $row[$key] !== null)));

            foreach (array_chunk($rows, self::CHUNK) as $chunk) {
                DB::table($table)->upsert($chunk, $keys, array_values(array_diff(array_keys($chunk[0]), $keys)));
            }

            $current = array_map(fn (array $row): string => self::keyOf($row, $keys), $rows);
            $staleIds = DB::table($table)->get(['id', ...$keys])
                ->filter(fn (object $existing): bool => ! in_array(self::keyOf((array) $existing, $keys), $current, true))
                ->pluck('id')
                ->all();

            foreach (array_chunk($staleIds, self::CHUNK) as $chunk) {
                DB::table($table)->whereIn('id', $chunk)->delete();
            }

            return ['inserted_or_updated' => count($rows), 'deleted' => count($staleIds)];
        });
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $keys
     */
    private static function keyOf(array $row, array $keys): string
    {
        return implode('|', array_map(fn (string $key): string => (string) $row[$key], $keys));
    }
}
