<?php

use App\Http\Controllers\BpmBridgeController;
use App\Mail\ProformaMail;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/admin/manuale', function () {
    return response()->download(public_path('docs/Manuale Proforma.pdf'), 'Manuale Proforma.pdf');
})->name('filament.admin.pages.manuale');

/*
 * Route::get('/', function () {
 *     return view('welcome');
 * });
 */
Route::get('/mail-preview', function () {
    $user = User::first();

    return new ProformaMail($user);
});

// La rotta riceve l'ID del soggetto (es: l'agente) e il token di sicurezza nei parametri
Route::get('/bpm-landing/{subject_id}', [BpmBridgeController::class, 'handle'])
    ->middleware('throttle:10,1')
    ->name('bpm.landing');
