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
            Log::info('Récupération des informations utilisateur', ['user_id' => $id]);
            
            $user = User::findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des informations utilisateur', [
                'error' => $e->getMessage(),
                'user_id' => $id
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);
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
                'user' => $user
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