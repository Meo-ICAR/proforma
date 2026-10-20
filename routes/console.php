<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Jobs\ImportaDaMediafacile;
use Illuminate\Support\Facades\Schedule;

// Import notturno da MediaFacile (ultimi 60 giorni), in coda; a fine lavoro gli utenti in MEDIAFACILE_NOTIFY_EMAILS ricevono la notifica.
Schedule::job(new ImportaDaMediafacile)->dailyAt('03:00')->withoutOverlapping();

Schedule::command('coge:sync-monthly')->monthlyOn(1, '01:00');

// Genera le prime note serali dalle regole configurate, poi le invia a Business Central.
Schedule::command('primanota:generate')->dailyAt('20:00')->withoutOverlapping();
// Alle 20:20 (non 20:15): lo scheduler gira ogni 10 minuti (deploy/cron.d-unico-scheduler di unico-core), un orario fuori da quei multipli non scatterebbe.
Schedule::command('coge:sync-primenote')->dailyAt('20:20')->withoutOverlapping();

// Load custom commands
