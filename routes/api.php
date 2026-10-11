<?php

use App\Http\Controllers\Api\CheckStatusApiController;
use App\Http\Controllers\Api\LatestModelRecordApiController;
use App\Http\Controllers\Api\ModelFieldsApiController;
use App\Http\Controllers\Api\ModelFieldValueApiController;
use App\Http\Controllers\Api\UserLookupApiController;
use Illuminate\Support\Facades\Route;

// Consumato da UnicoBPM per configurare i processi (select dei campi
// disponibili) e per scrivere un campo senza accedere direttamente ai
// modelli di questa app. Protetto da chiave condivisa (header X-Api-Key,
// vedi services.bpm.api_key / App\Http\Middleware\VerifyBpmApiKey).
Route::middleware('bpm.auth')->group(function () {
    Route::get('/models/{model}/fields', [ModelFieldsApiController::class, 'show'])->name('api.models.fields');
    Route::get('/models/{model}/latest', [LatestModelRecordApiController::class, 'show'])->name('api.models.latest');
    Route::get('/models/{model}/{id}', [ModelFieldValueApiController::class, 'show'])->name('api.models.show-record');
    Route::patch('/models/{model}/{id}', [ModelFieldValueApiController::class, 'update'])->name('api.models.update-field');

    // Consumato da UnicoBPM per verificare se l'utente loggato ha un account anche qui.
    Route::get('/users/lookup', [UserLookupApiController::class, 'show'])->name('api.users.lookup');

    // I comandi lanciabili da UnicoBPM (whitelist in config/schedulable_commands.php, esito leggibile con GET /api/commands/runs/{id})
    // sono nel bridge di unico-core (UNICO_CORE_BRIDGE): vedi vendor/meo-icar/unico-core/docs/bridge-api.md.

    // Consumato da UnicoBPM per conoscere lo stato (valore + severity) dei controlli; è
    // UnicoBPM, in base alla RACI, a decidere chi avvisare e con quale email.
    Route::get('/checks', [CheckStatusApiController::class, 'index'])->name('api.checks.index');
    Route::get('/checks/{command}', [CheckStatusApiController::class, 'show'])->name('api.checks.show');
});
