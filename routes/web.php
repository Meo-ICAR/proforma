<?php

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
    $user = \App\Models\User::first();
    return new App\Mail\ProformaMail($user);
});
