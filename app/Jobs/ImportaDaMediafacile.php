<?php

namespace App\Jobs;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

/**
 * Importa da MediaFacile pratiche e provvigioni degli ultimi 60 giorni (prima le pratiche, a cui si agganciano le provvigioni) e
 * avvisa gli utenti indicati in `services.mediafacile.notify_emails`, con quante righe sono nuove o modificate. Lo lancia lo
 * scheduler (routes/console.php): l'importazione non parte più dalla pagina. I numeri sono quelli del riepilogo che i comandi stampano.
 */
class ImportaDaMediafacile implements ShouldQueue
{
    use Queueable;

    public const GIORNI = 60;

    public int $timeout = 1800;

    public int $tries = 1;

    public function handle(): void
    {
        $opzioni = [
            '--start-date' => now()->subDays(self::GIORNI)->toDateString(),
            '--end-date' => now()->toDateString(),
        ];
        $righe = [];
        $fallito = false;

        foreach (['pratiche:import-api' => 'Pratiche', 'provvigioni:import-api' => 'Provvigioni'] as $comando => $etichetta) {
            try {
                $esito = Artisan::call($comando, $opzioni);
            } catch (\Throwable $e) {
                $esito = 1;
                report($e);
            }

            $output = Artisan::output();
            $ultima = trim((string) collect(explode("\n", trim($output)))->last());

            if ($esito !== 0) {
                $fallito = true;
                $righe[] = "{$etichetta}: importazione non riuscita. {$ultima}";

                continue;
            }

            if (preg_match('/Imported: (\d+), Updated: (\d+), Errors: (\d+)/', $output, $trovate) === 1) {
                $righe[] = "{$etichetta}: {$trovate[1]} nuove, {$trovate[2]} modificate, {$trovate[3]} errori.";
            } else {
                $righe[] = "{$etichetta}: {$ultima}";
            }
        }

        $destinatari = User::query()->whereIn('email', (array) config('services.mediafacile.notify_emails'))->get();

        $notifica = Notification::make()
            ->title($fallito ? 'Importazione da MediaFacile completata con errori' : 'Importazione da MediaFacile completata')
            ->body(implode("\n", $righe))
            ->color($fallito ? 'danger' : 'success')
            ->icon($fallito ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
            ->toDatabase();

        $destinatari->each->notifyNow($notifica);
    }
}
