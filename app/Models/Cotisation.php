<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'montant',
        'date_debut',
        'date_fin',
        'status'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'montant' => 'decimal:2'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('statut_paiement', 'date_paiement')
            ->withTimestamps();
    }
} 