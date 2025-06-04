<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UpdateUserController extends Controller
{
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            // Log détaillé des données reçues
            Log::info('Données reçues pour la mise à jour', [
                'user_id' => $id,
                'request_all' => $request->all(),
                'request_files' => $request->allFiles(),
                'content_type' => $request->header('Content-Type')
            ]);

            // Récupérer l'utilisateur
            $user = User::findOrFail($id);
            
            // Log des données actuelles
            Log::info('Données actuelles de l\'utilisateur', [
                'user_id' => $id,
                'current_data' => $user->toArray()
            ]);

            // Validation des données
            $validatedData = $request->validate([
                'nom' => 'sometimes|string|max:255',
                'prenom' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,'.$id,
                'telephone' => 'sometimes|string|max:20',
                'photo' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
                'etablissement' => 'sometimes|string|max:255',
                'parcours' => 'sometimes|string|max:255',
                'niveau' => 'sometimes|string|max:255',
                'promotion' => 'sometimes|string|max:255',
                'role' => 'sometimes|string|max:255',
                'sous_role' => 'sometimes|string|max:255|nullable'
            ]);

            Log::info('Données validées', [
                'user_id' => $id,
                'validated_data' => $validatedData
            ]);

            // Traitement de la photo
            if ($request->hasFile('photo')) {
                Log::info('Traitement de la nouvelle photo');
                
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                    Log::info('Ancienne photo supprimée');
                }
                
                $photoPath = $request->file('photo')->store('photos', 'public');
                $validatedData['photo'] = $photoPath;
                Log::info('Nouvelle photo enregistrée', ['path' => $photoPath]);
            }

            // Sauvegarder les modifications
            $oldData = $user->toArray();
            
            // Mettre à jour uniquement les champs qui ont été envoyés
            foreach ($validatedData as $key => $value) {
                if ($value !== null) {
                    $user->$key = $value;
                }
            }
            
            // Vérifier si des changements ont été effectués
            $changes = $user->getDirty();
            Log::info('Changements détectés', [
                'changes' => $changes
            ]);

            if (count($changes) > 0) {
                $user->save();
                Log::info('Modifications sauvegardées en base de données');
            } else {
                Log::info('Aucune modification à sauvegarder');
            }

            // Recharger l'utilisateur depuis la base de données
            $user = $user->fresh();
            
            Log::info('État final de l\'utilisateur', [
                'final_data' => $user->toArray()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Informations mises à jour avec succès',
                'user' => $user,
                'changes' => $changes
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la mise à jour', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }
} 