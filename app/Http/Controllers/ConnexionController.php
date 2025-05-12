<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ConnexionController extends Controller
{
    /**
     * Gérer la connexion d'un utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Les informations de connexion sont invalides.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérification des informations d'identification
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Génération du token JWT
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Connexion réussie',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Email ou mot de passe incorrect.'
            ], 401);
        }
    }
}
