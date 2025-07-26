<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserNotificationController extends Controller
{
    // Lister les notifications de l'utilisateur connecté
    public function index()
    {
        Log::info('UserNotificationController@index - Début de la requête');
        
        $user = Auth::user();
        Log::info('UserNotificationController@index - Utilisateur récupéré', [
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'authenticated' => Auth::check()
        ]);
        
        if (!$user) {
            Log::error('UserNotificationController@index - Aucun utilisateur authentifié');
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 401);
        }
        
        $notifications = $user->notifications()
            ->with('admin')
            ->orderByDesc('notification_user.created_at')
            ->get();
            
        Log::info('UserNotificationController@index - Notifications récupérées', [
            'count' => $notifications->count(),
            'user_id' => $user->id
        ]);

        $formattedNotifications = $notifications->map(function ($notif) {
            return [
                'id' => $notif->id,
                'title' => $notif->title,
                'message' => $notif->message,
                'type' => $notif->type ?? 'info',
                'created_at' => $notif->pivot->created_at->toDateTimeString(),
                'is_read' => $notif->pivot->is_read,
                'admin_name' => $notif->admin ? $notif->admin->prenom . ' ' . $notif->admin->nom : 'Administrateur',
                'admin_avatar' => $notif->admin ? $notif->admin->photo_url : null,
            ];
        });
        
        Log::info('UserNotificationController@index - Notifications formatées', [
            'count' => $formattedNotifications->count()
        ]);
        
        return $formattedNotifications;
    }

    // Marquer une notification comme lue
    public function markAsRead($id)
    {
        $user = Auth::user();
        $updated = $user->notifications()->updateExistingPivot($id, ['is_read' => true]);
        if ($updated) {
            return response()->json(['message' => 'Notification marquée comme lue.']);
        } else {
            return response()->json(['message' => 'Notification non trouvée.'], Response::HTTP_NOT_FOUND);
        }
    }

    // Supprimer une notification pour l'utilisateur
    public function destroy($id)
    {
        $user = Auth::user();
        $deleted = $user->notifications()->detach($id);
        if ($deleted) {
            return response()->json(['message' => 'Notification supprimée.']);
        } else {
            return response()->json(['message' => 'Notification non trouvée.'], Response::HTTP_NOT_FOUND);
        }
    }
} 