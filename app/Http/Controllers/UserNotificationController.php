<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    // Lister les notifications de l'utilisateur connecté
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->withPivot('is_read')->orderByDesc('notification_user.created_at')->get();
        return response()->json($notifications);
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