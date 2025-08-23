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
        $redirectUri = env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/api/auth/google/callback');       
        // Configuration pour désactiver la vérification SSL en développement
        $config = [
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]
        ];
        
        return \Laravel\Socialite\Facades\Socialite::driver('google')
            ->stateless()
            ->redirectUrl($redirectUri)
            ->setHttpClient(new \GuzzleHttp\Client($config))
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            \Log::info('=== Google OAuth Callback Started ===');
            \Log::info('Request URL:', ['url' => request()->fullUrl()]);
            \Log::info('Request Method:', ['method' => request()->method()]);
            \Log::info('Request Headers:', ['headers' => request()->headers->all()]);
            
            $redirectUri = env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/api/auth/google/callback');
            \Log::info('Redirect URI:', ['redirect_uri' => $redirectUri]);
            
            // Configuration pour désactiver la vérification SSL en développement
            $config = [
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ]
            ];
            
            \Log::info('Attempting to get Google user...');
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->stateless()
                ->redirectUrl($redirectUri)
                ->setHttpClient(new \GuzzleHttp\Client($config))
                ->user();
                
            \Log::info('Google OAuth - Utilisateur Google récupéré', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId()
            ]);
            
            \Log::info('Google user details:', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar()
            ]);
                
            // Vérifier si l'utilisateur existe déjà
            $user = User::where('email', $googleUser->getEmail())->first();
            $isNewUser = false;

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
                    'password' => bcrypt(str()->random(24)), // Mot de passe temporaire
                    'provider' => 'google', // Ajouter le provider
                    'provider_id' => $googleUser->getId(), // Ajouter l'ID Google
                    'role' => $isAdmin ? 'admin' : 'Membre', // rôle selon l'email
                    'sous_role' => '',
                    'etablissement' => '',
                    'parcours' => '', 
                    'niveau' => '', 
                    'promotion' => '',
                    'telephone' => '', 
                ]);
                $isNewUser = true;
                
                // Associer le nouvel utilisateur à toutes les cotisations existantes
                if (!$isAdmin) {
                    $cotisations = \App\Models\Cotisation::all();
                    foreach ($cotisations as $cotisation) {
                        $user->cotisations()->attach($cotisation->id, [
                            'statut_paiement' => 'Non payé'
                        ]);
                    }
                }
                
                \Log::info('Google OAuth - Nouvel utilisateur créé', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            } else {
                // Si l'utilisateur existe déjà, mettre à jour ses informations Google
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'photo' => $googleUser->getAvatar(),
                ]);
                
                \Log::info('Google OAuth - Utilisateur existant mis à jour', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                // Vérifier si c'est un utilisateur Google qui n'a pas encore défini de mot de passe
                if ($user->provider === 'google' && !$user->password_set) {
                    $isNewUser = true;
                }
            }

            // S'assurer que l'utilisateur possède toutes les cotisations existantes
            if (!$user->role || $user->role !== 'admin') {
                $cotisations = \App\Models\Cotisation::all();
                $cotisationIds = $user->cotisations()->pluck('cotisation_id')->toArray();
                foreach ($cotisations as $cotisation) {
                    if (!in_array($cotisation->id, $cotisationIds)) {
                        $user->cotisations()->attach($cotisation->id, [
                            'statut_paiement' => 'Non payé'
                        ]);
                    }
                }
            }

            // Générer un token Laravel Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;
            
            \Log::info('Google OAuth - Token généré', [
                'user_id' => $user->id,
                'token_length' => strlen($token)
            ]);

            // Rediriger vers le frontend avec le token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            \Log::info('Frontend URL:', ['frontend_url' => $frontendUrl]);
            
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
                \Log::info('Google OAuth - Redirection vers création de mot de passe', [
                    'url' => $redirectUrl,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'user_data_encoded' => $userDataEncoded
                ]);
                return redirect()->away($redirectUrl);
            }

            // Pour les utilisateurs existants, utiliser le cache
            $userData = [
                'id' => $user->id,
                'email' => $user->email,
                'nom' => $user->nom ?? 'Utilisateur',
                'prenom' => $user->prenom ?? 'Utilisateur',
                'provider' => $user->provider ?? 'google'
            ];
            \Cache::put('temp_user_data_' . $user->id, $userData, 300); // 5 minutes
            \Log::info('User data cached:', ['cache_key' => 'temp_user_data_' . $user->id, 'data' => $userData]);
            $redirectUrl = "$frontendUrl/google-callback?token=$token&user_id=$user->id";
            \Log::info('Google OAuth - Redirection vers dashboard avec données en cache', [
                'url' => $redirectUrl,
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return redirect()->away($redirectUrl);

        } catch (\Exception $e) {
            \Log::error('Erreur Google OAuth: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Google OAuth - Détails de l\'erreur', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
                'client_id' => env('GOOGLE_CLIENT_ID'),
                'frontend_url' => env('FRONTEND_URL'),
                'app_debug' => env('APP_DEBUG'),
                'app_env' => env('APP_ENV'),
                'error_code' => $e->getCode(),
                'previous_error' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
            ]);
            
            // Log complet de l'erreur pour débogage
            \Log::error('Google OAuth - Erreur complète', [
                'full_message' => $e->getMessage(),
                'sql_state' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->away("$frontendUrl/login?error=google_auth_failed&details=" . urlencode($e->getMessage()));
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
                'role' => 'Membre',
                'sous_role' => '',
                'etablissement' => '', // Champ obligatoire
                'parcours' => '', // Champ obligatoire
                'niveau' => '', // Champ obligatoire
                'promotion' => '', // Champ obligatoire
                'telephone' => '', // Champ obligatoire
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