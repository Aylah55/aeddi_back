<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
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
