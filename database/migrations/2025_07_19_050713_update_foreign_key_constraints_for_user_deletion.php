<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte
            $table->dropForeign(['user_id']);
            
            // Recréer avec suppression en cascade
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Supprimer la contrainte avec cascade
            $table->dropForeign(['user_id']);
            
            // Recréer l'ancienne contrainte
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
