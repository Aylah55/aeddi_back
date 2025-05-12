<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConnexionController;
use App\Http\Controllers\UserController;

Route::get('/ping', function () {
    return response()->json(['message' => 'Frontend et Backend sont connectés !']);
});
Route::post('/inscription', [AuthController::class, 'register']);
Route::post('/login', [ConnexionController::class, 'login']);
Route::middleware('auth:sanctum')->post('/profile/{id}/update', [UserController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

