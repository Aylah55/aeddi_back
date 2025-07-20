<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GetUserController extends Controller
{
    public function getUserInfo($id)
    {
        try {
            Log::info('Récupération des informations utilisateur', [
                'user_id' => $id,
                'request_user' => auth()->user() ? auth()->user()->id : 'non_authentifié',
                'headers' => request()->headers->all()
            ]);
            
            $user = User::findOrFail($id);
            
            Log::info('Utilisateur trouvé', [
                'user_id' => $user->id,
                'email' => $user->email,
                'nom' => $user->nom
            ]);
            
            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'telephone' => $user->telephone,
                    'photo' => $user->photo_url,
                    'etablissement' => $user->etablissement,
                    'parcours' => $user->parcours,
                    'niveau' => $user->niveau,
                    'promotion' => $user->promotion,
                    'role' => $user->role,
                    'sous_role' => $user->sous_role,
                    'provider' => $user->provider,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Utilisateur non trouvé', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des informations utilisateur', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des informations utilisateur'
            ], 500);
        }
    }

    public function updateUserInfo(Request $request, $id)
    {
        try {
            Log::info('Mise à jour des informations utilisateur', [
                'user_id' => $id,
                'data' => $request->except('password')
            ]);

            $user = User::findOrFail($id);
            
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

            // Traitement de la photo si elle est fournie
            if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo si elle existe
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }
                
                $photoPath = $request->file('photo')->store('photos', 'public');
                $validatedData['photo'] = $photoPath;
            }

            $user->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Informations mises à jour avec succès',
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'telephone' => $user->telephone,
                    'photo' => $user->photo_url,
                    'etablissement' => $user->etablissement,
                    'parcours' => $user->parcours,
                    'niveau' => $user->niveau,
                    'promotion' => $user->promotion,
                    'role' => $user->role,
                    'sous_role' => $user->sous_role,
                    'provider' => $user->provider,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour des informations utilisateur', [
                'error' => $e->getMessage(),
                'user_id' => $id
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour des informations'
            ], 500);
        }
    }
} 