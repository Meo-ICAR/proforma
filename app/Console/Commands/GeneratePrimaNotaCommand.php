<?php

namespace App\Console\Commands;

use App\Models\PrimaNotaConfig;
use App\Models\PrimaNotaEntry;
use Illuminate\Console\Command;

class GeneratePrimaNotaCommand extends Command
{
    protected $signature = 'primanota:generate';

    protected $description = 'Genera le prime note serali in base alle regole configurate';

    public function handle(): void
    {
        $configs = PrimaNotaConfig::where('is_active', true)->get();

        foreach ($configs as $config) {
            $modelClass = $config->model_type;
            $valueField = $config->value_field;
            $dateField = $config->date_field;

            $query = $modelClass::query()
                // 1. Campo valore presente e con il segno richiesto dalla regola:
                // is_positive = true -> solo > 0, false -> solo < 0, null -> qualsiasi segno (comunque escluso lo zero).
                ->whereNotNull($valueField)
                ->when(
                    $config->is_positive === null,
                    fn ($q) => $q->where($valueField, '!=', 0),
                    fn ($q) => $q->where($valueField, $config->is_positive ? '>' : '<', 0)
                )

                // 2. Ignora il regresso: considera solo record creati/modificati da effective_from in poi
                ->when($config->effective_from, function ($q) use ($config) {
                    $q->where('updated_at', '>=', $config->effective_from);
                })

                // 3. Se c'è un campo data evento, verifica che sia stato valorizzato e sia <= oggi
                ->when($dateField, function ($q) use ($dateField) {
                    $q->whereNotNull($dateField)->whereDate($dateField, '<=', now());
                })

                // 4. Controlla che NON esista già una prima nota per QUESTA specifica regola
                ->whereDoesntHave('primaNotaEntries', function ($q) use ($config) {
                    $q->where('prima_nota_config_id', $config->id);
                });

            foreach ($query->get() as $record) {
                // Determina la data per la prima nota
                $dataRegistrazione = ($dateField && $record->{$dateField})
                    ? $record->{$dateField}
                    : now()->toDateString();

                PrimaNotaEntry::create([
                    'prima_nota_config_id' => $config->id,
                    'data' => $dataRegistrazione,
                    'importo' => $record->{$valueField},
                    'conto_dare' => $config->conto_dare,
                    'conto_avere' => $config->conto_avere,
                    'record_type' => get_class($record),
                    'record_id' => $record->id,
                ]);
            }
        }
    }
}
