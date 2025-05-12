<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'photo' => 'nullable|image|max:2048',  // Validation de la photo
        ]);

        // Si la validation échoue, retour des erreurs
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Si une photo est téléchargée, on la stocke dans le dossier public/photos
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
        }

        // Création de l'utilisateur
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'photo' => $photoPath,  // Stockage du chemin de la photo
        ]);

        // Réponse avec les données de l'utilisateur et le lien vers la photo
        return response()->json([
            'message' => 'Utilisateur enregistré avec succès',
            'user' => $user,
            'photo_url' => asset('storage/' . $user->photo),  // URL de la photo
        ]);
    }
}
