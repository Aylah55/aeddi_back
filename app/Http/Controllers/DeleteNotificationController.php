<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeleteNotificationController extends Controller
{
    /**
     * Supprimer une notification par son ID pour l'utilisateur authentifié.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $notification = Notification::where('id', $id)->where('user_id', $user->id)->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notification non trouvée.'
            ], Response::HTTP_NOT_FOUND);
        }

        $notification->delete();
        return response()->json([
            'message' => 'Notification supprimée.'
        ], Response::HTTP_OK);
    }
} 