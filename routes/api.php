<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GetUserController;
use App\Http\Controllers\UpdateUserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\GetAllUserController;
use App\Http\Controllers\DeleteUserController;
use App\Http\Controllers\Api\GetActivityController;
use App\Http\Controllers\Api\CreateActivityController;
use App\Http\Controllers\Api\UpdateActivityController;
use App\Http\Controllers\Api\GetCotisationController;
use App\Http\Controllers\Api\CreateCotisationController;
use App\Http\Controllers\Api\UpdateCotisationController;
use App\Http\Controllers\Api\GetUserCotisationsController;
use App\Http\Controllers\Api\UpdateCotisationPaiementController;
use App\Http\Controllers\Api\GetUserCotisationsStatusController;
use App\Http\Controllers\Api\GetMyCotisationsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\MessageController;
use App\Models\Message;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Support\Facades\Password;

// Routes publiques
Route::get('/health', function() {
    return response()->json([
        'status' => 'OK',
        'message' => 'API Laravel fonctionne',
        'timestamp' => now(),
        'environment' => env('APP_ENV'),
        'debug' => env('APP_DEBUG')
    ]);
});

Route::get('/test-google-config', function() {
    return response()->json([
        'google_client_id' => env('GOOGLE_CLIENT_ID') ? 'Définie' : 'Manquante',
        'google_client_secret' => env('GOOGLE_CLIENT_SECRET') ? 'Définie' : 'Manquante',
        'google_redirect_uri' => env('GOOGLE_REDIRECT_URI') ? 'Définie' : 'Manquante',
        'frontend_url' => env('FRONTEND_URL') ? 'Définie' : 'Manquante',
        'app_url' => env('APP_URL'),
        'sanctum_domains' => env('SANCTUM_STATEFUL_DOMAINS')
    ]);
});

Route::get('/test-google-callback', function() {
    try {
        // Test simple pour voir si le contrôleur peut être instancié
        $controller = new \App\Http\Controllers\Auth\GoogleController();
        return response()->json([
            'status' => 'success',
            'message' => 'GoogleController peut être instancié',
            'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            'client_id' => env('GOOGLE_CLIENT_ID') ? 'Définie' : 'Manquante'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

Route::get('/test-google-redirect', function() {
    try {
        // Test de la redirection Google
        $controller = new \App\Http\Controllers\Auth\GoogleController();
        $redirectResponse = $controller->redirectToGoogle();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Redirection Google fonctionne',
            'response_type' => get_class($redirectResponse),
            'status_code' => $redirectResponse->getStatusCode()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/test-socialite-config', function() {
    try {
        // Test de la configuration Socialite
        $config = config('services.google');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Configuration Socialite récupérée',
            'config' => [
                'client_id' => $config['client_id'] ? 'Définie' : 'Manquante',
                'client_secret' => $config['client_secret'] ? 'Définie' : 'Manquante',
                'redirect' => $config['redirect'] ? 'Définie' : 'Manquante'
            ],
            'env_vars' => [
                'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID') ? 'Définie' : 'Manquante',
                'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET') ? 'Définie' : 'Manquante',
                'GOOGLE_REDIRECT_URI' => env('GOOGLE_REDIRECT_URI') ? 'Définie' : 'Manquante'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

Route::get('/test-mail-config', function() {
    try {
        // Test de la configuration email
        $mailConfig = config('mail');
        
        return response()->json([
            'status' => 'success',
            'message' => 'Configuration email récupérée',
            'default_mailer' => $mailConfig['default'],
            'from_address' => $mailConfig['from']['address'],
            'from_name' => $mailConfig['from']['name'],
            'env_vars' => [
                'MAIL_MAILER' => env('MAIL_MAILER'),
                'MAIL_HOST' => env('MAIL_HOST'),
                'MAIL_PORT' => env('MAIL_PORT'),
                'MAIL_USERNAME' => env('MAIL_USERNAME') ? 'Définie' : 'Manquante',
                'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? 'Définie' : 'Manquante',
                'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
                'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
                'MAIL_FROM_NAME' => env('MAIL_FROM_NAME')
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

Route::get('/test-logs', function() {
    try {
        // Test d'écriture de logs
        \Log::info('Test log from API route', [
            'timestamp' => now(),
            'test_data' => 'This is a test log entry'
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Log test écrit avec succès',
            'timestamp' => now()->toISOString(),
            'log_file' => storage_path('logs/laravel.log')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/set-password', [AuthController::class, 'setPassword']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/test-email-exists', [ForgotPasswordController::class, 'testEmailExists']);
Route::get('/test', [TestController::class, 'test']);
Route::get('/test-cache', [GoogleController::class, 'testCache']);
Route::get('/check-user/{id}', [GoogleController::class, 'checkUser']);
Route::get('/temp-user-data/{id}', [GoogleController::class, 'getTempUserData']);
// Routes Google OAuth
Route::get('/auth/google/redirect', [GoogleController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']); // <-- Ajouté pour le flux OAuth classique
Route::post('/auth/google/callback', [GoogleController::class, 'handleGoogleSPA']);
// Routes Facebook OAuth
Route::get('/auth/facebook/redirect', [FacebookController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [FacebookController::class, 'handleFacebookCallback']);
Route::post('/auth/facebook/callback', [FacebookController::class, 'handleFacebookSPA']);
// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/{id}', [GetUserController::class, 'getUserInfo']);
    Route::put('/user/{id}', [UpdateUserController::class, 'update']);
    Route::get('/users', [GetAllUserController::class, 'index']);
    Route::delete('/users/{id}', DeleteUserController::class);
    Route::get('/test-auth', [GoogleController::class, 'testAuth']);
    
    // Activités
    Route::get('/activites', [GetActivityController::class, '__invoke']);
    Route::post('/activites', [CreateActivityController::class, '__invoke']);
    Route::put('/activite/{id}', UpdateActivityController::class);
    Route::delete('/activite/{id}', [App\Http\Controllers\DeleteActiviteController::class, 'destroy']);

    // Cotisations
    Route::get('/cotisations', [GetCotisationController::class, '__invoke']);
    Route::post('/cotisations', [CreateCotisationController::class, '__invoke']);
    Route::put('/cotisation/{id}', [UpdateCotisationController::class, '__invoke']);
    Route::delete('/cotisation/{id}', [App\Http\Controllers\DeleteCotisationController::class, 'destroy']);
    Route::get('/user/{id}/cotisations', [GetUserCotisationsController::class, '__invoke']);
    Route::get('/user/{id}/cotisations/status', [GetUserCotisationsStatusController::class, '__invoke']);
    Route::put('/cotisation/{cotisationId}/user/{userId}/paiement', [UpdateCotisationPaiementController::class, '__invoke']);
    Route::get('/my-cotisations', [GetMyCotisationsController::class, '__invoke']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications', [NotificationController::class, 'deleteAll']);
    Route::delete('/notification/{id}', [App\Http\Controllers\DeleteNotificationController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->post('/messages/send', [MessageController::class, 'send']);
Route::middleware('auth:sanctum')->get('/messages', function() {
    return Message::orderBy('sent_at', 'desc')->take(50)->get();
});

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'token' => 'required',
        'password' => 'required|confirmed|min:8',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password)
            ])->save();
        }
    );

    if ($status == Password::PASSWORD_RESET) {
        return response()->json(['message' => 'Mot de passe réinitialisé avec succès.']);
    } else {
        return response()->json(['message' => __($status)], 400);
    }
});

Route::middleware('auth:api')->group(function () {
    Route::get('/user/notifications', [App\Http\Controllers\UserNotificationController::class, 'index']);
    Route::patch('/user/notifications/{id}/read', [App\Http\Controllers\UserNotificationController::class, 'markAsRead']);
    Route::delete('/user/notifications/{id}', [App\Http\Controllers\UserNotificationController::class, 'destroy']);
});