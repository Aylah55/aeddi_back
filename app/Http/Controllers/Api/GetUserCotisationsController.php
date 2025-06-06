<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class GetUserCotisationsController extends Controller
{
    public function __invoke(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $cotisations = $user->cotisations()
                ->with(['pivot'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($cotisation) {
                    return [
                        'id' => $cotisation->id,
                        'nom' => $cotisation->nom,
                        'description' => $cotisation->description,
                        'montant' => $cotisation->montant,
                        'date_debut' => $cotisation->date_debut,
                        'date_fin' => $cotisation->date_fin,
                        'status' => $cotisation->status,
                        'statut_paiement' => $cotisation->pivot->statut_paiement,
                        'date_paiement' => $cotisation->pivot->date_paiement,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => $cotisations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des cotisations'
            ], 500);
        }
    }
} 