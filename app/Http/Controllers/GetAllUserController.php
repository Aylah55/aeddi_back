<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class GetAllUserController extends Controller
{
    public function index()
    {
        try {
            $users = User::select(
                'id',
                'photo',
                'nom',
                'prenom',
                'email',
                'etablissement',
                'role',
                'sous_role',
                'telephone',
                'parcours',
                'niveau',
                'promotion',
                'created_at',
                'updated_at'
            )->get();

            // Ajouter l'URL de la photo pour chaque utilisateur
            $usersWithPhotoUrl = $users->map(function ($user) {
                $userData = $user->toArray();
                $userData['photo_url'] = $user->photo_url;
                return $userData;
            });

            return response()->json([
                'status' => 'success',
                'users' => $usersWithPhotoUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 