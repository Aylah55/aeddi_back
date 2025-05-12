<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
    public function show()
    {
        $user = Auth::user();

        return response()->json([
            'user' => $user
        ]);
    }
    
    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation échouée', 'errors' => $validator->errors()], 422);
        }

        $user->nom = $request->nom;
        $user->prenom = $request->prenom;
        $user->email = $request->email;

        if ($request->hasFile('photo')) {
            \Log::info('Début de l\'upload de la photo');
            \Log::info('Type de fichier: ' . $request->file('photo')->getClientMimeType());
            \Log::info('Taille du fichier: ' . $request->file('photo')->getSize() . ' bytes');

            // Supprimer l'ancienne photo si existante
            if ($user->photo) {
                \Log::info('Suppression de l\'ancienne photo: ' . $user->photo);
                Storage::disk('public')->delete($user->photo);
            }

            $photoPath = $request->file('photo')->store('photos', 'public');
            \Log::info('Photo sauvegardée avec chemin: ' . $photoPath);
            $user->photo = $photoPath;
        }

        $user->save();
        \Log::info('Profil mis à jour avec succès');
        \Log::info('Informations utilisateur sauvegardées:', [
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'email' => $user->email,
            'photo' => $user->photo
        ]);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }
}
