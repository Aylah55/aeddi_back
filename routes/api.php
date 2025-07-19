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

    // Cotisations
    Route::get('/cotisations', [GetCotisationController::class, '__invoke']);
    Route::post('/cotisations', [CreateCotisationController::class, '__invoke']);
    Route::put('/cotisation/{id}', [UpdateCotisationController::class, '__invoke']);
    Route::get('/user/{id}/cotisations', [GetUserCotisationsController::class, '__invoke']);
    Route::get('/user/{id}/cotisations/status', [GetUserCotisationsStatusController::class, '__invoke']);
    Route::put('/cotisation/{cotisationId}/user/{userId}/paiement', [UpdateCotisationPaiementController::class, '__invoke']);
    Route::get('/my-cotisations', [GetMyCotisationsController::class, '__invoke']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications', [NotificationController::class, 'deleteAll']);
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