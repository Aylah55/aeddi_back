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
        \Log::info('=== DÉBUT DE L\'INSCRIPTION ===');
        \Log::info('Données reçues:', $request->all());
        \Log::info('Headers:', $request->headers->all());

        try {
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
                \Log::error('=== ERREUR DE VALIDATION ===');
                \Log::error('Détails:', $validator->errors()->toArray());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            \Log::info('=== VALIDATION RÉUSSIE ===');

            // Si une photo est téléchargée, on la stocke
            $photoPath = null;
            if ($request->hasFile('photo')) {
                \Log::info('Photo détectée, tentative de stockage');
                try {
                    $photoPath = $request->file('photo')->store('photos', 'public');
                    \Log::info('Photo stockée avec succès:', ['path' => $photoPath]);
                } catch (\Exception $e) {
                    \Log::error('Erreur lors du stockage de la photo:', ['error' => $e->getMessage()]);
                    throw new \Exception('Erreur lors du stockage de la photo: ' . $e->getMessage());
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

            \Log::info('=== TENTATIVE DE CRÉATION DE L\'UTILISATEUR ===');
            \Log::info('Données:', array_except($userData, ['password']));

            $user = User::create($userData);
            
            if (!$user) {
                throw new \Exception('Échec de la création de l\'utilisateur dans la base de données');
            }

            \Log::info('=== UTILISATEUR CRÉÉ AVEC SUCCÈS ===', ['user_id' => $user->id]);

            // Création du token
            try {
                $token = $user->createToken('auth_token')->plainTextToken;
                \Log::info('=== TOKEN CRÉÉ ===', ['token_length' => strlen($token)]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la création du token:', ['error' => $e->getMessage()]);
                throw new \Exception('Erreur lors de la création du token: ' . $e->getMessage());
            }

            // Préparation de la réponse
            $response = [
                'status' => 'success',
                'message' => 'Utilisateur enregistré avec succès',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'photo_url' => $user->photo ? asset('storage/' . $user->photo) : null,
                ]
            ];

            \Log::info('=== RÉPONSE PRÉPARÉE ===');
            \Log::info('Structure de la réponse:', array_keys($response));
            \Log::info('Structure user:', array_keys($response['user']));

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('=== ERREUR LORS DE L\'INSCRIPTION ===');
            \Log::error('Message:', ['error' => $e->getMessage()]);
            \Log::error('Trace:', ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'inscription: ' . $e->getMessage()
            ], 500);
        }
    }
}
