<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
Route::get('/db-test', function () {
    try {
        DB::connection()->getPdo();
        return "Connexion réussie à la base PostgreSQL !";
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur de connexion',
            'message' => $e->getMessage()
        ], 500);
    }
});
