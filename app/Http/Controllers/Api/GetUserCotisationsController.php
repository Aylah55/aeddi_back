<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetUserCotisationsController extends Controller
{
    public function __invoke(Request $request, $userId)
    {
        try {
            Log::info('Début de la récupération des cotisations', ['user_id' => $userId]);

            $user = User::findOrFail($userId);
            Log::info('Utilisateur trouvé', ['user' => $user->toArray()]);

            // Vérifier si la relation cotisations existe
            if (!method_exists($user, 'cotisations')) {
                throw new \Exception('La relation cotisations n\'existe pas dans le modèle User');
            }

            // Récupérer les cotisations
            $query = $user->cotisations();
            Log::info('Requête de cotisations construite', ['sql' => $query->toSql()]);

            $cotisationsRaw = $query->orderBy('created_at', 'desc')->get();
            Log::info('Cotisations brutes récupérées', ['count' => $cotisationsRaw->count()]);

            // Transformer les données
            $cotisations = $cotisationsRaw->map(function ($cotisation) {
                try {
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
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la transformation d\'une cotisation', [
                        'cotisation_id' => $cotisation->id ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            });

            Log::info('Cotisations transformées avec succès', [
                'count' => $cotisations->count(),
                'first_item' => $cotisations->first()
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $cotisations
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Utilisateur non trouvé', ['user_id' => $userId]);
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des cotisations:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des cotisations',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }
} 