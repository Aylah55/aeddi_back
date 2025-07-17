<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Notifications\CustomResetPassword;

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
        'sous_role',
        'provider',
        'provider_id'
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

    public function cotisations()
    {
        return $this->belongsToMany(Cotisation::class)
            ->withPivot('statut_paiement', 'date_paiement')
            ->withTimestamps();
    }
    public function notifications()
{
    return $this->hasMany(Notification::class);
}

    public function sendPasswordResetNotification($token)
    {
        $url = 'http://localhost:3000/create-password?token=' . $token . '&email=' . urlencode($this->email);
        $this->notify(new CustomResetPassword($url));
    }
}
