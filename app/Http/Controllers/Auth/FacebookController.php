<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FacebookController extends Controller
{
    public function redirectToFacebook()
    {
        $redirectUri = env('FACEBOOK_REDIRECT_URI', 'http://localhost:8000/api/auth/facebook/callback');
        
        return \Laravel\Socialite\Facades\Socialite::driver('facebook')
            ->stateless()
            ->redirectUrl($redirectUri)
            ->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $redirectUri = env('FACEBOOK_REDIRECT_URI', 'http://localhost:8000/api/auth/facebook/callback');
            
            $facebookUser = \Laravel\Socialite\Facades\Socialite::driver('facebook')
                ->stateless()
                ->redirectUrl($redirectUri)
                ->user();
                
            \Log::info('Facebook OAuth - Utilisateur Facebook récupéré', [
                'email' => $facebookUser->getEmail(),
                'name' => $facebookUser->getName(),
                'id' => $facebookUser->getId()
            ]);
                
            // Vérifier si l'utilisateur existe déjà
            $user = User::where('email', $facebookUser->getEmail())->first();
            $isNewUser = false;

            if (!$user) {
                // Vérifier si l'email correspond à un admin connu
                $isAdmin = in_array($facebookUser->getEmail(), [
                    'admin@gmail.com', // Email admin existant en base
                ]);

                // Créer un nouvel utilisateur
                $user = User::create([
                    'nom' => $facebookUser->getName() ?? 'Utilisateur',
                    'prenom' => $facebookUser->getName() ?? 'Utilisateur',
                    'email' => $facebookUser->getEmail(),
                    'photo' => $facebookUser->getAvatar(),
                    'password' => bcrypt(str()->random(24)), // Mot de passe temporaire
                    'provider' => 'facebook', // Ajouter le provider
                    'provider_id' => $facebookUser->getId(), // Ajouter l'ID Facebook
                    'role' => $isAdmin ? 'admin' : 'user', // rôle selon l'email
                ]);
                $isNewUser = true;
                
                \Log::info('Facebook OAuth - Nouvel utilisateur créé', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            } else {
                // Si l'utilisateur existe déjà, mettre à jour ses informations Facebook
                $user->update([
                    'provider' => 'facebook',
                    'provider_id' => $facebookUser->getId(),
                    'photo' => $facebookUser->getAvatar(),
                ]);
                
                \Log::info('Facebook OAuth - Utilisateur existant mis à jour', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                // Vérifier si c'est un utilisateur Facebook qui n'a pas encore défini de mot de passe
                if ($user->provider === 'facebook' && !$user->password_set) {
                    $isNewUser = true;
                }
            }

            // Générer un token Laravel Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;
            
            \Log::info('Facebook OAuth - Token généré', [
                'user_id' => $user->id,
                'token_length' => strlen($token)
            ]);

            // Rediriger vers le frontend avec le token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            
            if ($isNewUser) {
                // Pour les nouveaux utilisateurs, transmettre les données essentielles dans l'URL
                $userData = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nom' => $user->nom ?? 'Utilisateur',
                    'prenom' => $user->prenom ?? 'Utilisateur'
                ];
                
                $userDataEncoded = base64_encode(json_encode($userData));
                $redirectUrl = "$frontendUrl/create-password?token=$token&user_id=$user->id&user_data=$userDataEncoded&new_user=true";
                
                \Log::info('Facebook OAuth - Redirection vers création de mot de passe', [
                    'url' => $redirectUrl,
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                return redirect()->away($redirectUrl);
            } else {
                // Pour les utilisateurs existants, utiliser le cache
                $userData = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'nom' => $user->nom ?? 'Utilisateur',
                    'prenom' => $user->prenom ?? 'Utilisateur',
                    'provider' => $user->provider ?? 'facebook'
                ];
                
                \Cache::put('temp_user_data_' . $user->id, $userData, 300); // 5 minutes
                
                // Rediriger vers le dashboard
                $redirectUrl = "$frontendUrl/facebook-callback?token=$token&user_id=$user->id";
                
                \Log::info('Facebook OAuth - Redirection vers dashboard avec données en cache', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                return redirect()->away($redirectUrl);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur Facebook OAuth: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->away("$frontendUrl/login?error=facebook_auth_failed");
        }
    }

    public function handleFacebookSPA(Request $request)
    {
        $accessToken = $request->input('access_token');
        if (!$accessToken) {
            return response()->json(['error' => 'Missing access token'], 400);
        }

        // Vérifier le token Facebook côté serveur
        $facebookResponse = Http::get('https://graph.facebook.com/me', [
            'fields' => 'id,name,email,picture',
            'access_token' => $accessToken
        ]);

        if (!$facebookResponse->ok()) {
            return response()->json(['error' => 'Invalid Facebook token'], 401);
        }

        $facebookUser = $facebookResponse->json();

        // $facebookUser contient : id, name, email, picture, etc.
        $user = User::where('email', $facebookUser['email'])->first();

        if (!$user) {
            $user = User::create([
                'nom' => $facebookUser['name'] ?? 'Utilisateur',
                'prenom' => $facebookUser['name'] ?? 'Utilisateur',
                'email' => $facebookUser['email'],
                'photo' => $facebookUser['picture']['data']['url'] ?? null,
                'password' => Hash::make(Str::random(24)),
                'provider' => 'facebook',
                'provider_id' => $facebookUser['id'],
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

    public function testAuth(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié',
                    'headers' => $request->headers->all()
                ], 401);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur authentifié',
                'user' => $user,
                'token_info' => [
                    'has_token' => $request->bearerToken() ? true : false,
                    'token_length' => $request->bearerToken() ? strlen($request->bearerToken()) : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du test d\'authentification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkUser($id)
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non trouvé',
                    'user_id' => $id
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur trouvé',
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la vérification de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTempUserData($userId)
    {
        try {
            $userData = \Cache::get('temp_user_data_' . $userId);
            
            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Données utilisateur temporaires non trouvées'
                ], 404);
            }
            
            // Supprimer les données temporaires après les avoir récupérées
            \Cache::forget('temp_user_data_' . $userId);
            
            return response()->json([
                'status' => 'success',
                'user' => $userData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des données utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testCache()
    {
        try {
            $testData = ['test' => 'data', 'timestamp' => now()];
            \Cache::put('test_cache', $testData, 60);
            
            $retrievedData = \Cache::get('test_cache');
            
            return response()->json([
                'status' => 'success',
                'cache_working' => $retrievedData === $testData,
                'test_data' => $testData,
                'retrieved_data' => $retrievedData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du test du cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 