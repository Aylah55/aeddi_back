<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Les informations de connexion sont incorrectes.'],
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Permettre aux utilisateurs Google de créer un mot de passe local
     */
    public function setPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé.'], 404);
        }

        // Vérifier que c'est un compte Google (ou sans mot de passe défini)
        if ($user->provider === 'google' || !$user->password_set) {
            $user->password = bcrypt($request->password);
            $user->password_set = true; // Marquer que le mot de passe est défini
            $user->save();

            return response()->json([
                'message' => 'Mot de passe créé avec succès. Vous pouvez maintenant vous connecter avec email/mot de passe.'
            ]);
        }

        return response()->json([
            'message' => 'Ce compte a déjà un mot de passe défini. Utilisez la fonction "mot de passe oublié" si nécessaire.'
        ], 400);
    }

    public function user(Request $request)
    {
        try {
            return response()->json([
                'status' => 'success',
                'user' => $request->user()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 