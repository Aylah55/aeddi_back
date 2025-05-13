<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ConnexionController extends Controller
{
    public function login(Request $request)
    {
        // Répondre aux pré-requêtes OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200)
                ->header('Access-Control-Allow-Origin', 'https://aeddi-antsiranana.onrender.com')
                ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        // Validation des champs requis
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            // Tentative d'authentification
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email ou mot de passe incorrect.'
                ], 401)
                    ->header('Access-Control-Allow-Origin', 'https://aeddi-antsiranana.onrender.com')
                    ->header('Access-Control-Allow-Credentials', 'true');
            }


            // Récupération de l'utilisateur authentifié
            $user = User::where('email', $request->email)->firstOrFail();

            // Création d'un nouveau token d'authentification
            $token = $user->createToken('auth_token')->plainTextToken;

            // Mise à jour de la date de dernière connexion
            $user->last_login_at = now();
            $user->save();

            // Réponse avec le token et les informations de l'utilisateur
            return response()->json([
                'status' => 'success',
                'message' => 'Connexion réussie',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                    'last_login_at' => $user->last_login_at,
                ]
            ])
                ->header('Access-Control-Allow-Origin', 'https://aeddi-antsiranana.onrender.com')
                ->header('Access-Control-Allow-Credentials', 'true');

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la connexion',
                'error' => $e->getMessage()
            ], 500)
                ->header('Access-Control-Allow-Origin', 'https://aeddi-antsiranana.onrender.com')
                ->header('Access-Control-Allow-Credentials', 'true');
        }
    }
}
