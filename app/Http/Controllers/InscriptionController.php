<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class InscriptionController extends Controller
{
    public function register(Request $request)
    {
        try {
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
                'password' => 'required|string|min:8',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Traitement de la photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
            }

            // Création de l'utilisateur avec tous les champs
            $userData = $request->except('photo', 'password');
            $userData['password'] = Hash::make($request->password);
            $userData['photo'] = $photoPath;

            $user = User::create($userData);

            // Création du token d'authentification
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Inscription réussie',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (\Exception $e) {
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