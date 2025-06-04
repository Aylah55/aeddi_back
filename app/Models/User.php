<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'photo',
        'etablissement',
        'parcours',
        'niveau',
        'promotion',
        'role',
        'sous_role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Ajout d'un mutateur pour s'assurer que les champs ne sont jamais null
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->fillable) && $value === null) {
            $value = '';
        }
        return parent::setAttribute($key, $value);
    }
}
