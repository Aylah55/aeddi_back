<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UpdateProfilUserController;

Route::get('/ping', function () {
    return response()->json(['message' => 'Frontend et Backend sont connectés !']);
});
Route::post('/inscription', [AuthController::class, 'register']);
Route::post('/login', [ConnexionController::class, 'login']);
Route::middleware('auth:sanctum')->get('/profile/{id}', [UserController::class, 'show']);
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
Route::put('/profile/{id}', [UpdateProfilUserController::class, 'update']);

