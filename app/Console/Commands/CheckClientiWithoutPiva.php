<?php

namespace App\Console\Commands;

use App\Models\Clienti;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckClientiWithoutPiva extends Command
{
    protected $signature = 'clienti:check-missing-piva {--to=segreteria@races.it : Destinatario del reminder}';

    protected $description = 'Verifica che non ci siano istituti attivi (non fittizi) senza partita IVA e, se ce ne sono, invia un reminder';

    public function handle(): int
    {
        $names = Clienti::activeWithoutPiva()->pluck('name');

        if ($names->isEmpty()) {
            $this->info('Nessun istituto attivo senza partita IVA.');

            return self::SUCCESS;
        }

        $to = $this->option('to');

        Mail::raw(
            "Ci sono {$names->count()} istituti attivi senza partita IVA:\n\n- "
                .$names->implode("\n- ")
                ."\n\nCompletare l'anagrafica su ".route('filament.admin.resources.clientis.index', [
                    'filters' => ['piva' => ['value' => 0]],
                ]),
            fn ($message) => $message->to($to)->subject("Reminder: {$names->count()} istituti attivi senza partita IVA")
        );

        $this->warn("{$names->count()} istituti senza partita IVA: reminder inviato a {$to}.");

        return self::SUCCESS;
    }
}
