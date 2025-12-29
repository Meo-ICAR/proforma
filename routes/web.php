<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

/*
 * Route::get('/', function () {
 *     return view('welcome');
 * });
 */
Route::get('/mail-preview', function () {
    $user = \App\Models\User::first();
    return new App\Mail\ProformaMail($user);
});
