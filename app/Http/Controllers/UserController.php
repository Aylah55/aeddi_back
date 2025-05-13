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
    public function index()
    {
        try {
            // Vérifier si l'utilisateur est authentifié et a les droits d'administration
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Non autorisé. Veuillez vous connecter.'
                ], 401)->header('Access-Control-Allow-Origin', '*')
                  ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                  ->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
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
    public function show()
    {
        $user = Auth::user();
        $user->photo_url = $user->photo ? url('storage/' . $user->photo) : null;

        return response()->json([
            'user' => $user
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
        try {
            // Récupérer l'utilisateur authentifié
            $user = $request->user();
            
            // Supprimer le token d'accès actuel
            $user->tokens()->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Déconnexion réussie',
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Échec de la déconnexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
