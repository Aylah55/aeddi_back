<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\DB;

Route::get('/db-test', function () {
    try {
        DB::connection()->getPdo();
        return "Connexion à la base de données réussie !";
    } catch (\Exception $e) {
        return "Erreur de connexion : " . $e->getMessage();
    }
});
