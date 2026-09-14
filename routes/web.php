<?php

use App\Http\Controllers\BpmBridgeController;
use App\Mail\ProformaMail;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// Nome imposto da Filament per la rotta automatica della Page "Manuale" (slug 'manuale'):
// la sovrascriviamo qui per servire direttamente il PDF invece del componente Livewire.
Route::get('/admin/manuale', function () {
    return response()->download(resource_path('manuals/Manuale Proforma.pdf'), 'Manuale Proforma.pdf');
})->name('filament.admin.pages.manuale');

Route::get('/admin/manuale-oam', function () {
    return response()->download(resource_path('manuals/Manuale Proforma.pdf'), 'Manuale Proforma.pdf');
})->name('manuale-oam');

Route::get('/admin/manuale-contabile', function () {
    return response()->download(resource_path('manuals/Manuale_Utente_Contabili_Proforma.pdf'), 'Manuale_Utente_Contabili_Proforma.pdf');
})->name('manuale-contabile');

Route::get('/admin/manuale-tecnico', function () {
    return response()->file(resource_path('manuals/manuale-tecnico.html'));
})->name('manuale-tecnico');

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
