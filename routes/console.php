<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

Schedule::command('coge:sync-monthly')->monthlyOn(1, '01:00');

// Genera le prime note serali dalle regole configurate, poi le invia a Business Central.
Schedule::command('primanota:generate')->dailyAt('20:00')->withoutOverlapping();
Schedule::command('coge:sync-primenote')->dailyAt('20:15')->withoutOverlapping();

// Load custom commands
