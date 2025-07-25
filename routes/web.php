<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route de diagnostic pour identifier les problèmes
Route::get('/diagnostic', function () {
    $diagnostic = [];
    
    // Test de connexion à la base de données
    try {
        \DB::connection()->getPdo();
        $diagnostic['database'] = 'OK - Connexion réussie';
    } catch (\Exception $e) {
        $diagnostic['database'] = 'ERREUR - ' . $e->getMessage();
    }
    
    // Test des permissions sur storage
    $storageWritable = is_writable(storage_path());
    $logsWritable = is_writable(storage_path('logs'));
    $diagnostic['storage_permissions'] = [
        'storage' => $storageWritable ? 'OK' : 'ERREUR',
        'logs' => $logsWritable ? 'OK' : 'ERREUR'
    ];
    
    // Test des variables d'environnement
    $diagnostic['env_vars'] = [
        'APP_ENV' => env('APP_ENV'),
        'APP_DEBUG' => env('APP_DEBUG'),
        'APP_KEY' => env('APP_KEY') ? 'Définie' : 'Manquante',
        'DB_HOST' => env('DB_HOST'),
        'DB_DATABASE' => env('DB_DATABASE'),
        'DB_USERNAME' => env('DB_USERNAME'),
        'DB_PASSWORD' => env('DB_PASSWORD') ? 'Définie' : 'Manquante'
    ];
    
    // Test des migrations
    try {
        $pendingMigrations = \Artisan::call('migrate:status');
        $diagnostic['migrations'] = 'OK - Vérifié';
    } catch (\Exception $e) {
        $diagnostic['migrations'] = 'ERREUR - ' . $e->getMessage();
    }
    
    // Test des caches
    try {
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
        $diagnostic['caches'] = 'OK - Vidés';
    } catch (\Exception $e) {
        $diagnostic['caches'] = 'ERREUR - ' . $e->getMessage();
    }
    
    return response()->json($diagnostic);
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

Route::get('/reset-password/{token}', function ($token) {
    return 'Lien de réinitialisation reçu. Token : ' . $token;
})->name('password.reset');

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