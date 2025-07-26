<?php

namespace App\Http\Controllers;

use App\Models\Cotisation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeleteCotisationController extends Controller
{
    /**
     * Supprimer une cotisation par son ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $cotisation = Cotisation::find($id);
        if (!$cotisation) {
            return response()->json([
                'message' => 'Cotisation non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $cotisation->delete();
            return response()->json([
                'message' => 'Cotisation supprimée avec succès.'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la suppression de la cotisation.",
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 