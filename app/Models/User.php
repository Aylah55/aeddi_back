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
        'password_set',
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

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function notificationsAsAdmin()
    {
        return $this->hasMany(Notification::class, 'admin_id');
    }

    // Méthode pour supprimer l'utilisateur et toutes ses données associées
    public static function boot()
    {
        parent::boot();

        // Avant de supprimer un utilisateur
        static::deleting(function ($user) {
            // Supprimer toutes les notifications de l'utilisateur
            $user->notifications()->delete();
            
            // Supprimer tous les messages de l'utilisateur
            $user->messages()->delete();
            
            // Supprimer les notifications créées par l'utilisateur (admin)
            $user->notificationsAsAdmin()->delete();
            
            // Détacher l'utilisateur de toutes ses cotisations
            $user->cotisations()->detach();
        });
    }

    public function sendPasswordResetNotification($token)
    {
        $frontendUrl = env('FRONTEND_URL', 'https://aeddi-front.onrender.com');
        
        // Créer les données utilisateur pour le frontend
        $userData = [
            'id' => $this->id,
            'email' => $this->email,
            'nom' => $this->nom ?? 'Utilisateur',
            'prenom' => $this->prenom ?? 'Utilisateur',
            'provider' => $this->provider ?? 'email'
        ];
        
        $userDataEncoded = base64_encode(json_encode($userData));
        
        $url = $frontendUrl . '/create-password?token=' . $token . 
               '&email=' . urlencode($this->email) . 
               '&user_id=' . $this->id . 
               '&user_data=' . $userDataEncoded . 
               '&new_user=false';
               
        $this->notify(new CustomResetPassword($url));
    }

    // Accesseur pour l'URL de la photo
    public function getPhotoUrlAttribute()
    {
        if (!$this->photo) {
            return 'https://i.pravatar.cc/100?img=' . rand(1, 70);
        }

        // Si c'est une URL externe (Google, Facebook)
        if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
            return $this->photo;
        }

        // Si c'est un fichier local stocké
        return Storage::disk('public')->url($this->photo);
    }
}
