<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;
        
        \Log::info('Demande de réinitialisation de mot de passe', ['email' => $email]);

        // Vérifier si l'utilisateur existe dans la base de données
        $user = User::where('email', $email)->first();

        if (!$user) {
            // L'email n'existe pas dans la base de données
            \Log::info('Email non trouvé dans la base de données', ['email' => $email]);
            return response()->json([
                'message' => 'Cet email n\'existe pas dans notre base de données. Veuillez vérifier votre adresse email ou créer un compte.'
            ], 404); // Retourner 404 pour indiquer que l'email n'existe pas
        }

        \Log::info('Utilisateur trouvé', [
            'user_id' => $user->id,
            'email' => $user->email,
            'provider' => $user->provider,
            'password_set' => $user->password_set
        ]);

        // Vérifier si c'est un compte Google (qui n'a pas de mot de passe local)
        if ($user->provider === 'google' && !$user->password_set) {
            \Log::info('Compte Google sans mot de passe local', ['user_id' => $user->id]);
            return response()->json([
                'message' => 'Ce compte utilise la connexion Google. Veuillez vous connecter avec Google ou créer un mot de passe local d\'abord.'
            ], 400);
        }

        // L'utilisateur existe et peut réinitialiser son mot de passe
        \Log::info('Envoi de l\'email de réinitialisation', ['user_id' => $user->id]);
        
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                \Log::info('Email de réinitialisation envoyé avec succès', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'Un email de réinitialisation a été envoyé à votre adresse email.'
                ]);
            } else {
                \Log::error('Erreur lors de l\'envoi de l\'email de réinitialisation', [
                    'user_id' => $user->id,
                    'status' => $status
                ]);
                return response()->json([
                    'message' => 'Impossible d\'envoyer l\'email de réinitialisation. Veuillez réessayer.'
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Exception lors de l\'envoi de l\'email de réinitialisation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testEmailExists(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $email = $request->email;
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'exists' => false,
                'message' => 'Email non trouvé dans la base de données'
            ]);
        }
        
        return response()->json([
            'exists' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'provider' => $user->provider,
                'password_set' => $user->password_set,
                'can_reset_password' => !($user->provider === 'google' && !$user->password_set)
            ],
            'message' => 'Email trouvé dans la base de données'
        ]);
    }
}