<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->with('admin')
            ->orderByDesc('notification_user.created_at')
            ->get();

        return $notifications->map(function ($notif) {
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
    }

    // Créer une notification pour tous les utilisateurs
    // Utilise Notification::createForAllUsers() qui attache automatiquement à tous les utilisateurs
    public function createForAllUsers(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'string|in:info,success,warning,error',
        ]);

        $admin = Auth::user();
        
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $notification = Notification::createForAllUsers([
            'admin_id' => $admin->id,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type ?? 'info',
        ]);

        return response()->json([
            'message' => 'Notification créée et distribuée à tous les utilisateurs',
            'notification' => $notification
        ], 201);
    }

    // Créer une notification pour des utilisateurs spécifiques
    // Utilise Notification::createForUsers() qui attache automatiquement aux utilisateurs spécifiés
    public function createForUsers(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'string|in:info,success,warning,error',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $admin = Auth::user();
        
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $notification = Notification::createForUsers([
            'admin_id' => $admin->id,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type ?? 'info',
        ], $request->user_ids);

        return response()->json([
            'message' => 'Notification créée et distribuée aux utilisateurs sélectionnés',
            'notification' => $notification
        ], 201);
    }

    // Créer une notification pour un utilisateur spécifique
    // Utilise Notification::createForUser() qui utilise user_id et attache automatiquement
    public function createForUser(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'string|in:info,success,warning,error',
            'user_id' => 'required|exists:users,id',
        ]);

        $admin = Auth::user();
        
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $notification = Notification::createForUser([
            'admin_id' => $admin->id,
            'title' => $request->title,
            'message' => $request->message,
            'type' => $request->type ?? 'info',
        ], $request->user_id);

        return response()->json([
            'message' => 'Notification créée et distribuée à l\'utilisateur spécifié',
            'notification' => $notification
        ], 201);
    }

    // Marquer une notification comme lue
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['message' => 'Notification non trouvée'], 404);
        }

        $user->notifications()->updateExistingPivot($id, ['is_read' => true]);

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    // Marquer toutes les notifications comme lues
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $user->notifications()->updateExistingPivot($user->notifications()->pluck('id')->toArray(), ['is_read' => true]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues']);
    }

    // Supprimer une notification
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $user->notifications()->detach($id);

        return response()->json(['message' => 'Notification supprimée']);
    }

    // Supprimer toutes les notifications de l'utilisateur
    public function deleteAll(Request $request)
    {
        $user = $request->user();
        $user->notifications()->detach(); // Détacher seulement, ne pas supprimer les notifications

        return response()->json(['message' => 'Toutes les notifications ont été supprimées.']);
    }
}
