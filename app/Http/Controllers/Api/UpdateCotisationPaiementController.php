<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cotisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateCotisationPaiementController extends Controller
{
    public function __invoke(Request $request, $cotisationId, $userId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'statut_paiement' => 'required|string|in:Payé,Non payé'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($userId);
            $cotisation = Cotisation::findOrFail($cotisationId);

            $user->cotisations()->updateExistingPivot($cotisationId, [
                'statut_paiement' => $request->statut_paiement,
                'date_paiement' => $request->statut_paiement === 'Payé' ? now() : null
            ]);

            return response()->json([
                'message' => 'Statut de paiement mis à jour avec succès',
                'data' => [
                    'user_id' => $userId,
                    'cotisation_id' => $cotisationId,
                    'statut_paiement' => $request->statut_paiement,
                    'date_paiement' => $request->statut_paiement === 'Payé' ? now() : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour du statut de paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 