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
        // Répondre aux pré-requêtes OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization, X-CSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        // Vérifier si l'utilisateur est authentifié
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Non autorisé. Veuillez vous connecter.'
            ], 401)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
        }

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
                'status' => 'error',
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
        }


        try {
            // Trouver l'utilisateur
            $user = User::findOrFail($id);

            // Vérifier que l'utilisateur peut modifier ce profil
            if ($authUser->id != $user->id && !$authUser->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous n\'êtes pas autorisé à modifier ce profil.'
                ], 403)->withHeaders([
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                    'Access-Control-Allow-Credentials' => 'true'
                ]);
            }


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
                'status' => 'success',
                'message' => 'Profil mis à jour avec succès',
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'photo' => $user->photo ? asset('storage/' . $user->photo) : null,
                    'updated_at' => $user->updated_at,
                ]
            ])->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Allow-Credentials' => 'true'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour du profil',
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
