<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            // Utiliser une transaction pour s'assurer que tout est supprimé ou rien
            DB::beginTransaction();
            
            try {
                Log::info('Suppression de l\'utilisateur et de ses données associées', [
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);

                // Supprimer explicitement toutes les données associées
                
                // 1. Supprimer les notifications de l'utilisateur
                $user->notifications()->delete();
                Log::info('Notifications supprimées pour l\'utilisateur', ['user_id' => $user->id]);
                
                // 2. Supprimer les notifications créées par l'utilisateur (admin)
                $user->notificationsAsAdmin()->delete();
                Log::info('Notifications créées par l\'utilisateur supprimées', ['user_id' => $user->id]);
                
                // 3. Supprimer les messages de l'utilisateur
                $user->messages()->delete();
                Log::info('Messages supprimés pour l\'utilisateur', ['user_id' => $user->id]);
                
                // 4. Détacher l'utilisateur de toutes ses cotisations
                $user->cotisations()->detach();
                Log::info('Cotisations détachées pour l\'utilisateur', ['user_id' => $user->id]);
                
                // 5. Supprimer l'utilisateur lui-même
                $user->delete();
                Log::info('Utilisateur supprimé avec succès', ['user_id' => $user->id]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Utilisateur et toutes ses données associées supprimés avec succès'
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Erreur lors de la suppression de l\'utilisateur', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Erreur dans DeleteUserController', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}