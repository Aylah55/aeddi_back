<?php

namespace App\Http\Controllers;

use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeleteActiviteController extends Controller
{
    /**
     * Supprimer une activité par son ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $activite = Activite::find($id);
        if (!$activite) {
            return response()->json([
                'message' => 'Activité non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $activite->delete();
            return response()->json([
                'message' => 'Activité supprimée avec succès.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la suppression de l'activité.",
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 