<?php

use App\Http\Controllers\Api\CommandDispatchApiController;
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
    Route::get('/models/{model}/{id}', [ModelFieldValueApiController::class, 'show'])->name('api.models.show-record');
    Route::patch('/models/{model}/{id}', [ModelFieldValueApiController::class, 'update'])->name('api.models.update-field');

    // Consumato da UnicoBPM per verificare se l'utente loggato ha un account anche qui.
    Route::get('/users/lookup', [UserLookupApiController::class, 'show'])->name('api.users.lookup');

    // Permette a uno scheduler esterno di lanciare on-demand i comandi Artisan/servizi
    // di questa app (whitelist in config/schedulable_commands.php). L'esecuzione è
    // accodata (RunArtisanCommandJob) e la risposta torna subito con 202 Accepted.
    Route::get('/commands', [CommandDispatchApiController::class, 'index'])->name('api.commands.index');
    Route::post('/commands/{command}', [CommandDispatchApiController::class, 'store'])->name('api.commands.store');
});
