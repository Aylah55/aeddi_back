<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetUserCotisationsStatusController extends Controller
{
    public function __invoke(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            Log::info('Début de la récupération des statuts de cotisation', ['user_id' => $userId]);
            
            // Récupérer toutes les cotisations de l'utilisateur avec leur statut
            $query = $user->cotisations()
                ->select('cotisations.id', 'cotisation_user.statut_paiement');
                
            Log::info('Requête SQL', ['sql' => $query->toSql()]);
            
            $results = $query->get();
            Log::info('Résultats bruts', ['results' => $results->toArray()]);
            
            $cotisationsStatus = $results->pluck('pivot.statut_paiement', 'id')->toArray();
            Log::info('Statuts finaux', ['cotisationsStatus' => $cotisationsStatus]);

            return response()->json([
                'status' => 'success',
                'data' => $cotisationsStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statuts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des statuts de cotisation'
            ], 500);
        }
    }
} 