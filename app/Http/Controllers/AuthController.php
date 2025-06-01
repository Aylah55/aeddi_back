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
        // Répondre aux pré-requêtes OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', 'https://aeddi-antsiranana.onrender.com')
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, X-Requested-With, Authorization, X-CSRF-TOKEN, X-XSRF-TOKEN, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

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
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422)->withHeaders([
                'Access-Control-Allow-Origin' => 'https://aeddi-antsiranana.onrender.com',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
        }

        try {
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

            // Création du token d'authentification
            $token = $user->createToken('auth_token')->plainTextToken;

            // Réponse avec les données de l'utilisateur et le token
            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur enregistré avec succès',
                'user' => $user,
                'photo_url' => $user->photo ? asset('storage/' . $user->photo) : null,
                'token' => $token,
                'token_type' => 'Bearer',
            ])->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Allow-Credentials' => 'true'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
        }
    }
}
