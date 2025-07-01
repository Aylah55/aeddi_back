<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// Route Sanctum pour le CSRF token
Route::get('/sanctum/csrf-cookie', function () {
    $origin = request()->header('Origin');
    $allowedOrigins = ['http://localhost:3000', 'https://aeddi-front.onrender.com'];
    
    $response = response()->json(['message' => 'CSRF cookie set']);
    
    if (in_array($origin, $allowedOrigins)) {
        $response->withHeaders([
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept'
        ]);
    }
    
    return $response;
});

// Route OPTIONS pour les preflight requests
Route::options('/sanctum/csrf-cookie', function () {
    $origin = request()->header('Origin');
    $allowedOrigins = ['http://localhost:3000', 'https://aeddi-front.onrender.com'];
    
    $response = response('', 200);
    
    if (in_array($origin, $allowedOrigins)) {
        $response->withHeaders([
            'Access-Control-Allow-Origin' => $origin,
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept'
        ]);
    }
    
    return $response;
});

Route::get('/db-test', function() {
    try {
        DB::connection()->getPdo();
        $version = DB::select('SELECT version() as version')[0]->version;
        return response()->json([
            'status' => 'success',
            'database' => DB::getDatabaseName(),
            'version' => $version,
            'tables' => DB::select('SHOW TABLES')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'solution' => [
                '1. Vérifiez les identifiants dans .env',
                '2. Activez "Remote MySQL" dans cPanel InfinityFree',
                '3. Essayez avec MySQLi: '.function_exists('mysqli_connect')
            ]
        ], 500);
    }
});