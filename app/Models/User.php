<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject; // ➕ AJOUT DE CETTE LIGNE

class User extends Authenticatable implements JWTSubject // ➕ AJOUT DE L'INTERFACE
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['nom', 'prenom', 'email', 'password', 'photo'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ➕ MÉTHODES OBLIGATOIRES POUR JWT
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Identifiant utilisateur (souvent l’ID)
    }

    public function getJWTCustomClaims()
    {
        return []; // Tu peux ajouter ici des infos personnalisées dans le token
    }
}
