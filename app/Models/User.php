<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject; // ➕ AJOUT DE CETTE LIGNE
use Laravel\Sanctum\Sanctum; // ➕ AJOUT POUR LA GESTION DES TOKENS SANCTUM

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
    
    /**
     * Get the tokens that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens()
    {
        return $this->hasMany(Sanctum::$personalAccessTokenModel, 'tokenable_id', 'id');
    }
}
