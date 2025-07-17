<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DeleteUserController extends Controller
{
    public function __invoke($id)
    {
        try {
            // Vérifier si l'utilisateur connecté est admin
            $currentUser = auth()->user();
            if (!$currentUser || $currentUser->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Seuls les administrateurs peuvent supprimer des utilisateurs.'
                ], 403);
            }

            // Empêcher l'admin de se supprimer lui-même
            if ($currentUser->id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer votre propre compte.'
                ], 400);
            }

            $user = User::findOrFail($id);
            
            // Empêcher la suppression d'autres admins
            if ($user->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer un autre administrateur.'
                ], 400);
            }

            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}