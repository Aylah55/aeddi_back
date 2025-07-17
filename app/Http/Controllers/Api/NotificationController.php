<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = $user->notifications()->with('admin')->latest()->get();

        return $notifications->map(function ($notif) {
            return [
                'id' => $notif->id,
                'title' => $notif->title,
                'message' => $notif->message,
                'created_at' => $notif->created_at->toDateTimeString(),
                'is_read' => $notif->is_read,
                'admin_name' => $notif->admin ? $notif->admin->prenom . ' ' . $notif->admin->nom : 'Administrateur',
                'admin_avatar' => $notif->admin && $notif->admin->photo
                    ? url($notif->admin->photo)
                    : 'https://i.pravatar.cc/100?img=' . rand(1, 70),
            ];
        });
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $user->notifications()->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['message' => 'Toutes les notifications ont été marquées comme lues.']);
    }

    public function deleteAll(Request $request)
    {
        $user = $request->user();
        $user->notifications()->delete();

        return response()->json(['message' => 'Toutes les notifications ont été supprimées.']);
    }
}
