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

            return response()->json([
                'status' => 'success',
                'users' => $users
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