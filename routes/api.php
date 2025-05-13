<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UpdateProfilUserController;

// Gestion des requêtes OPTIONS pour CORS
Route::options('/{any}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-Auth-Token, X-Requested-With, Authorization, X-XSRF-TOKEN, X-CSRF-TOKEN')
        ->header('Access-Control-Allow-Credentials', 'true');
})->where('any', '.*');

Route::get('/ping', function () {
    return response()->json(['message' => 'Frontend et Backend sont connectés !']);
});
Route::post('/inscription', [AuthController::class, 'register']);
Route::post('/login', [ConnexionController::class, 'login']);
// Routes protégées nécessitant une authentification
Route::middleware('auth:sanctum')->group(function () {
    // Récupérer le profil d'un utilisateur spécifique
    Route::get('/profile/{id}', [UserController::class, 'show']);
    
    // Récupérer tous les utilisateurs (pour les administrateurs)
    Route::get('/users', [UserController::class, 'index']);
    
    // Déconnexion
    Route::post('/logout', [UserController::class, 'logout']);
});
Route::put('/profile/{id}', [UpdateProfilUserController::class, 'update']);

