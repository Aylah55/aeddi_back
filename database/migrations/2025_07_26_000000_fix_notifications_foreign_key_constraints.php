<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Supprimer la contrainte existante
            $table->dropForeign(['user_id']);
            
            // Recréer la contrainte avec onDelete('set null')
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Supprimer la contrainte
            $table->dropForeign(['user_id']);
            
            // Recréer la contrainte originale
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}; 