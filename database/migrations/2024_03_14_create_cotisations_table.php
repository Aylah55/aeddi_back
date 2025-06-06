<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cotisations', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->decimal('montant', 10, 2);
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->enum('status', ['À payer', 'En cours', 'Payé'])->default('À payer');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cotisations');
    }
}; 