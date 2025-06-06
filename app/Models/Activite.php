<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activite extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'date_debut',
        'date_fin',
        'status'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime'
    ];

    public static $rules = [
        'nom' => 'required|string|max:255',
        'description' => 'nullable|string',
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after_or_equal:date_debut',
        'status' => 'required|in:En cours,Terminé,À venir',
    ];
}
