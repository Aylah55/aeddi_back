<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InscriptionController;

// Route d'inscription
Route::post('/inscription', [InscriptionController::class, 'register']);
