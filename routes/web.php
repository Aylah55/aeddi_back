<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
Route::get('/db-test', function () {
    try {
        DB::connection()->getPdo();
        return "Connexion à la base de données réussie !";
    } catch (\Exception $e) {
        return "Erreur de connexion : " . $e->getMessage();
    }
});
