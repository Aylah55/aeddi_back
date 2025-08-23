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
        // Mettre à jour tous les utilisateurs qui ont des valeurs null pour les champs obligatoires
        DB::table('users')
            ->whereNull('etablissement')
            ->update(['etablissement' => '']);

        DB::table('users')
            ->whereNull('parcours')
            ->update(['parcours' => '']);

        DB::table('users')
            ->whereNull('niveau')
            ->update(['niveau' => '']);

        DB::table('users')
            ->whereNull('promotion')
            ->update(['promotion' => '']);

        DB::table('users')
            ->whereNull('telephone')
            ->update(['telephone' => '']);

        DB::table('users')
            ->whereNull('sous_role')
            ->update(['sous_role' => '']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cette migration ne peut pas être inversée car nous ne savons pas quelles étaient les valeurs originales
        // Les champs resteront des chaînes vides
    }
};
