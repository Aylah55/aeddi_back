<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GetActivityController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $activites = Activite::orderBy('date_debut', 'desc')->get();
            
            // Formater les dates pour l'affichage
            $formattedActivities = $activites->map(function($activite) {
                return [
                    'id' => $activite->id,
                    'nom' => $activite->nom,
                    'description' => $activite->description,
                    'date_debut' => $activite->date_debut,
                    'date_fin' => $activite->date_fin,
                    'status' => $activite->status,
                    'created_at' => $activite->created_at,
                    'updated_at' => $activite->updated_at
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $formattedActivities
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur récupération activités: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des activités',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
