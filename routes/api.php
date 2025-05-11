<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InscriptionController;
Route::get('/test', function () {
    return response()->json(['message' => 'Connexion backend OK']);
});

Route::post('/inscription', [InscriptionController::class, 'inscrire']);
