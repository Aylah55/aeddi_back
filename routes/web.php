<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
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