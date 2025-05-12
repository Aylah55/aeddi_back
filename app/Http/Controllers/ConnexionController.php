<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class ConnexionController extends Controller
{
    public function login(Request $request)
    {
        // Validation des champs requis
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Identifiants invalides'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'user' => auth()->user()
        ]);
    }
}
