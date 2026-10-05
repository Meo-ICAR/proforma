<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserLookupApiController extends Controller
{
    /**
     * Consumato da UnicoBPM per sapere se l'utente loggato ha anche un
     * account su questa app, per proporglielo come alternativa nella
     * dashboard "cambia app". Il modello User di questa app non implementa
     * FilamentUser: qualunque utente autenticato accede al pannello di
     * default, quindi l'esistenza dell'account basta.
     */
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        return response()->json([
            'exists' => User::where('email', $validated['email'])->exists(),
        ]);
    }
}
