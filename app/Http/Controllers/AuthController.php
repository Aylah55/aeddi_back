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
        \Log::info('Début de l\'inscription');
        \Log::info('Données reçues:', $request->all());

        // Validation des données d'entrée
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'photo' => 'nullable|image|max:2048',
        ]);

        // Si la validation échoue, retour des erreurs
        if ($validator->fails()) {
            \Log::error('Erreur de validation:', ['errors' => $validator->errors()->toArray()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            \Log::info('Validation réussie, création de l\'utilisateur');

            // Si une photo est téléchargée, on la stocke
            $photoPath = null;
            if ($request->hasFile('photo')) {
                \Log::info('Photo détectée, tentative de stockage');
                try {
                    $photoPath = $request->file('photo')->store('photos', 'public');
                    \Log::info('Photo stockée avec succès:', ['path' => $photoPath]);
                } catch (\Exception $e) {
                    \Log::error('Erreur lors du stockage de la photo:', ['error' => $e->getMessage()]);
                }
            }

            // Création de l'utilisateur
            $userData = [
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'photo' => $photoPath,
            ];

            \Log::info('Tentative de création de l\'utilisateur avec:', array_except($userData, ['password']));

            $user = User::create($userData);
            \Log::info('Utilisateur créé avec succès:', ['user_id' => $user->id]);

            // Création du token
            $token = $user->createToken('auth_token')->plainTextToken;
            \Log::info('Token créé avec succès');

            // Préparation de la réponse
            $response = [
                'status' => 'success',
                'message' => 'Utilisateur enregistré avec succès',
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'photo_url' => $user->photo ? asset('storage/' . $user->photo) : null,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ];

            \Log::info('Envoi de la réponse de succès');
            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'inscription:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
