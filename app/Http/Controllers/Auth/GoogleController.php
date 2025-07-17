<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        // Désactiver la vérification SSL pour le développement local
        if (app()->environment('local')) {
            \Http::withoutVerifying();
        }
        return Socialite::driver('google')
            ->stateless()
            ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            // Désactiver la vérification SSL pour le développement local
            if (app()->environment('local')) {
                \Http::withoutVerifying();
            }
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->user();

            // Vérifier si l'utilisateur existe déjà
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Vérifier si l'email correspond à un admin connu
                $isAdmin = in_array($googleUser->getEmail(), [
                    'admin@gmail.com', // Email admin existant en base
                ]);

                // Créer un nouvel utilisateur
                $user = User::create([
                    'nom' => $googleUser->getName() ?? 'Utilisateur',
                    'prenom' => $googleUser->getName() ?? 'Utilisateur',
                    'email' => $googleUser->getEmail(),
                    'photo' => $googleUser->getAvatar(),
                    'password' => bcrypt(str()->random(24)), // mot de passe aléatoire
                    'role' => $isAdmin ? 'admin' : 'user', // rôle selon l'email
                ]);
            }

            // Générer un token Laravel Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // Rediriger vers le frontend avec le token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect($frontendUrl . '/google-callback?token=' . $token . '&user_id=' . $user->id);

        } catch (\Exception $e) {
            \Log::error('Erreur Google OAuth: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect($frontendUrl . '/connexion?error=google_auth_failed');
        }
    }

    public function handleGoogleSPA(Request $request)
    {
        $credential = $request->input('credential');
        if (!$credential) {
            return response()->json(['error' => 'Missing credential'], 400);
        }

        // Vérifier le token Google côté serveur
        $googleResponse = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $credential
        ]);

        if (!$googleResponse->ok()) {
            return response()->json(['error' => 'Invalid Google token'], 401);
        }

        $googleUser = $googleResponse->json();

        // $googleUser contient : email, name, picture, sub (id Google), etc.
        $user = User::where('email', $googleUser['email'])->first();

        if (!$user) {
            $user = User::create([
                'nom' => $googleUser['name'] ?? 'Utilisateur',
                'prenom' => $googleUser['name'] ?? 'Utilisateur',
                'email' => $googleUser['email'],
                'photo' => $googleUser['picture'] ?? null,
                'password' => Hash::make(Str::random(24)),
                'provider' => 'google',
                'provider_id' => $googleUser['sub'],
                'role' => 'user',
            ]);
        }

        // Générer un token Laravel (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
}