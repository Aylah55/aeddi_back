<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    use HasFactory;

    // Indiquer les champs qui peuvent être remplis (mass assignment)
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'photo',
    ];

    // Si tu utilises un timestamp personnalisé
    public $timestamps = true;

    // Si la table est différente (si tu ne respectes pas la convention de nommage)
    protected $table = 'inscriptions';
}
