<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'is_read', // si tu veux gérer les notifications lues/non lues
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Notification.php

public function admin()
{
    return $this->belongsTo(User::class, 'admin_id');
}

public function users()
{
    return $this->belongsToMany(User::class, 'notification_user')
        ->withPivot('is_read')
        ->withTimestamps();
}

}
