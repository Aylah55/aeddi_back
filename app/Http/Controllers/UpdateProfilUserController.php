<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UpdateProfilUserController extends Controller
{
    public function update(Request $request, $id)
    {
        // Valider les données envoyées
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'photo' => 'nullable|image|max:2048', // Limiter la taille de l'image à 2MB
        ]);

        // Si la validation échoue, renvoyer une erreur
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        // Trouver l'utilisateur
        $user = User::findOrFail($id);

        // Mettre à jour les informations de l'utilisateur
        $user->nom = $request->input('nom');
        $user->prenom = $request->input('prenom');
        $user->email = $request->input('email');

        // Si une nouvelle photo est envoyée, la traiter
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }

            // Stocker la nouvelle photo
            $path = $request->file('photo')->store('users', 'public');
            $user->photo = $path;
        }

        // Sauvegarder les modifications dans la base de données
        $user->save();

        // Retourner la réponse avec l'utilisateur mis à jour
        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }
}
