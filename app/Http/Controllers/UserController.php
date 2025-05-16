<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Récupérer tous les utilisateurs (pour les administrateurs)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Répondre aux pré-requêtes OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization, X-CSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        try {
            // Vérifier si l'utilisateur est authentifié
            $user = $request->user();
            
            if (!$user) {
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
            
            // Récupérer tous les utilisateurs avec pagination
            $users = User::select('id', 'nom', 'prenom', 'email', 'photo', 'created_at')
                ->orderBy('nom')
                ->get()
                ->map(function($user) {
                    // Construire l'URL complète de la photo si elle existe
                    $photoUrl = null;
                    if ($user->photo) {
                        $photoUrl = str_contains($user->photo, 'http') ? $user->photo : url('storage/' . $user->photo);
                    }
                    
                    return [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'prenom' => $user->prenom,
                        'email' => $user->email,
                        'photo' => $photoUrl,
                        'date_inscription' => $user->created_at->format('d/m/Y')
                    ];
                });
            
            // Retourner la réponse avec les en-têtes CORS
            return response()->json([
                'status' => 'success',
                'users' => $users
            ])->header('Access-Control-Allow-Origin', '*')
              ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
              ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les informations d'un utilisateur spécifique
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        // Répondre aux pré-requêtes OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization, X-CSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        $user = $request->user();
        if (!$user) {
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

        $user->photo_url = $user->photo ? url('storage/' . $user->photo) : null;

        return response()->json([
            'status' => 'success',
            'user' => $user
        ])->withHeaders([
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }

    /**
     * Déconnecter l'utilisateur (invalider le token)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Répondre aux pré-requêtes OPTIONS
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization, X-CSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

<<<<<<< HEAD
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
=======
        try {
            // Récupérer l'utilisateur authentifié
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun utilisateur connecté.'
                ], 401)->withHeaders([
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                    'Access-Control-Allow-Credentials' => 'true'
                ]);
            }
            
            // Supprimer le token d'accès actuel
            $user->tokens()->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Déconnexion réussie',
            ], 200)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la déconnexion',
                'error' => $e->getMessage()
            ], 500)->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization',
                'Access-Control-Allow-Credentials' => 'true'
            ]);
        }
>>>>>>> d3dfcaebd25473a63a9c55a70d884c4e131f6c98
    }
}
