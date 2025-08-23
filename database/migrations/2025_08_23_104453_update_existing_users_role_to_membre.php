<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mettre à jour tous les utilisateurs qui ont le role "user" vers "Membre"
        DB::table('users')
            ->where('role', 'user')
            ->update(['role' => 'Membre']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre à jour tous les utilisateurs qui ont le role "Membre" vers "user"
        DB::table('users')
            ->where('role', 'Membre')
            ->update(['role' => 'user']);
    }
};
