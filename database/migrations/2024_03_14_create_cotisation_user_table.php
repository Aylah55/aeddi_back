<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cotisation_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cotisation_id')->constrained()->onDelete('cascade');
            $table->enum('statut_paiement', ['Payé', 'Non payé'])->default('Non payé');
            $table->dateTime('date_paiement')->nullable();
            $table->timestamps();
            
            // Empêcher les doublons
            $table->unique(['user_id', 'cotisation_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cotisation_user');
    }
}; 