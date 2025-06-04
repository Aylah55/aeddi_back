<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreatUserController;
use App\Http\Controllers\GetUserController;
use App\Http\Controllers\UpdateUserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\GetAllUserController;

// Routes publiques
Route::post('/inscription', [CreatUserController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/{id}', [GetUserController::class, 'getUserInfo']);
    Route::put('/user/{id}', [UpdateUserController::class, 'update']);
    Route::get('/users', [GetAllUserController::class, 'index']);
});
