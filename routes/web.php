<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BpmBridgeController;
use App\Models\Document;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
    $user = \App\Models\User::first();
    return new App\Mail\ProformaMail($user);
});


// La rotta riceve l'ID del soggetto (es: l'agente) e il token di sicurezza nei parametri
Route::get('/bpm-landing/{subject_id}', [BpmBridgeController::class, 'handle'])
    ->name('bpm.landing');
