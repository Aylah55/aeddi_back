<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class GetUserCotisationsStatusController extends Controller
{
    public function __invoke(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Récupérer toutes les cotisations de l'utilisateur avec leur statut
            $cotisationsStatus = $user->cotisations()
                ->select('cotisations.id', 'cotisation_user.statut_paiement')
                ->get()
                ->pluck('pivot.statut_paiement', 'id')
                ->toArray();

            return response()->json([
                'status' => 'success',
                'data' => $cotisationsStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des statuts de cotisation'
            ], 500);
        }
    }
} 