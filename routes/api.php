<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UpdateProfilUserController;

// Middleware pour gérer les en-têtes CORS
$corsHeaders = [
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
    'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization, X-CSRF-TOKEN',
    'Access-Control-Allow-Credentials' => 'true'
];

// Gestion des requêtes OPTIONS pour CORS
Route::options('/{any}', function () use ($corsHeaders) {
    return response('', 200)->withHeaders($corsHeaders);
})->where('any', '.*');

// Route de test de connexion
Route::get('/ping', function () use ($corsHeaders) {
    return response()->json([
        'message' => 'Frontend et Backend sont connectés !',
        'status' => 'success'
    ])->withHeaders($corsHeaders);
});

// Routes d'authentification
Route::post('/inscription', [AuthController::class, 'register']);
Route::post('/login', [ConnexionController::class, 'login']);

// Routes protégées nécessitant une authentification
Route::middleware('auth:sanctum')->group(function () use ($corsHeaders) {
    // Récupérer le profil d'un utilisateur spécifique
    Route::get('/profile/{id}', [UserController::class, 'show']);
    
    // Récupérer tous les utilisateurs (pour les administrateurs)
    Route::get('/users', [UserController::class, 'index']);
    
    // Déconnexion
    Route::post('/logout', [UserController::class, 'logout']);
    
    // Mise à jour du profil
    Route::put('/profile/{id}', [UpdateProfilUserController::class, 'update']);
});

// Gestion des routes non trouvées
Route::fallback(function () use ($corsHeaders) {
    return response()->json([
        'status' => 'error',
        'message' => 'Route non trouvée.'
    ], 404)->withHeaders($corsHeaders);
});

