<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'admin_id',
        'title',
        'message',
        'type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Relation avec l'utilisateur destinataire (pour les notifications directes)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation avec l'admin qui a créé la notification
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Relation many-to-many avec les utilisateurs (pour les notifications distribuées)
    public function users()
    {
        return $this->belongsToMany(User::class, 'notification_user')
            ->withPivot('is_read')
            ->withTimestamps();
    }

    // Événement pour attacher automatiquement la notification à l'utilisateur si user_id est présent
    protected static function boot()
    {
        parent::boot();

        static::created(function ($notification) {
            // Si la notification a un user_id, l'attacher automatiquement dans la table pivot
            if ($notification->user_id) {
                $notification->users()->attach($notification->user_id, [
                    'is_read' => false,
                    'created_at' => $notification->created_at,
                    'updated_at' => $notification->updated_at,
                ]);
            }
        });
    }

    // Méthode pour distribuer une notification à tous les utilisateurs
    public static function createForAllUsers($data)
    {
        $notification = self::create([
            'admin_id' => $data['admin_id'],
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info',
        ]);

        // Récupérer tous les utilisateurs (sauf admin)
        $users = User::where('role', '!=', 'admin')->get();
        
        // Attacher la notification à tous les utilisateurs
        $notification->users()->attach($users->pluck('id')->toArray(), [
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $notification;
    }

    // Méthode pour distribuer une notification à des utilisateurs spécifiques
    public static function createForUsers($data, $userIds)
    {
        $notification = self::create([
            'admin_id' => $data['admin_id'],
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info',
        ]);

        // Attacher la notification aux utilisateurs spécifiés
        $notification->users()->attach($userIds, [
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $notification;
    }

    // Méthode pour créer une notification pour un utilisateur spécifique
    public static function createForUser($data, $userId)
    {
        $notification = self::create([
            'admin_id' => $data['admin_id'],
            'user_id' => $userId, // Utiliser user_id pour la notification directe
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'info',
        ]);

        // L'attachement se fait automatiquement via l'événement created
        return $notification;
    }
}
