<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/ping', function () {
    return response()->json(['message' => 'Frontend et Backend sont connectés !']);
});
Route::post('/inscription', [AuthController::class, 'register']);