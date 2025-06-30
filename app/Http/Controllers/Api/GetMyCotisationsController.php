<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class GetMyCotisationsController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            // Récupérer l'utilisateur connecté
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            Log::info('Récupération des cotisations pour l\'utilisateur connecté', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Récupérer les cotisations de l'utilisateur connecté
            $cotisations = $user->cotisations()
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
                        'statut_paiement' => $cotisation->pivot->statut_paiement ?? 'Non payé',
                        'date_paiement' => $cotisation->pivot->date_paiement,
                    ];
                });

            Log::info('Cotisations récupérées avec succès', [
                'user_id' => $user->id,
                'count' => $cotisations->count()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $cotisations
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des cotisations:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des cotisations'
            ], 500);
        }
    }
} 