<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class InscriptionController extends Controller
{
    public function register(Request $request)
    {
        try {
            Log::info('Début de l\'inscription', ['data' => $request->all()]);

            // Validation des données
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'etablissement' => 'required|string|in:ESP,DEGSP',
                'parcours' => 'required|string|in:EP,EII,EG,GESTION',
                'niveau' => 'required|string|in:L1,L2,L3,M1,M2',
                'role' => 'required|string|in:President,Membre de bureau,Membre',
                'sous_role' => 'required_if:role,Membre de bureau|string|in:Tresoriere,Vice_president,Commissaire au compte,Commission logement,Commission sport,Conseillé',
                'promotion' => 'required|string|in:2020,2021,2022,2023,2024,2025',
                'telephone' => 'required|string|max:20',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                Log::error('Erreur de validation', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Traitement de la photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                Log::info('Traitement de la photo');
                try {
                    $photoPath = $request->file('photo')->store('photos', 'public');
                    Log::info('Photo enregistrée', ['path' => $photoPath]);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de l\'enregistrement de la photo', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Création de l'utilisateur avec tous les champs
            $userData = $request->except('photo', 'password');
            $userData['password'] = Hash::make($request->password);
            $userData['photo'] = $photoPath;

            Log::info('Données utilisateur préparées', ['userData' => array_except($userData, ['password'])]);

            try {
                $user = User::create($userData);
                Log::info('Utilisateur créé', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::error('Erreur lors de la création de l\'utilisateur', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'userData' => array_except($userData, ['password'])
                ]);
                throw $e;
            }

            // Création du token d'authentification
            try {
                $token = $user->createToken('auth_token')->plainTextToken;
                Log::info('Token créé');
            } catch (\Exception $e) {
                Log::error('Erreur lors de la création du token', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Inscription réussie',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'inscription', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except('password')
            ]);

            // En cas d'erreur, supprimer la photo si elle a été uploadée
            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 