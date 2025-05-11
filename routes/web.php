<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
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


Route::get('/run-migrations', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return '✅ Migrations exécutées avec succès !';
    } catch (Exception $e) {
        return '❌ Erreur lors de la migration : ' . $e->getMessage();
    }
});
