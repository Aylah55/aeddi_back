<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserTable extends Migration
{
    public function up()
    {
        DB::table('users')->insert([
            'nom' => 'Admin',
            'prenom' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'etablissement' => 'AEDDI',
            'parcours' => 'Administrateur',
            'niveau' => 'Supérieur',
            'promotion' => '2025',
            'telephone' => '0000000000',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        DB::table('users')->where('email', 'admin@gmail.com')->delete();
    }
}