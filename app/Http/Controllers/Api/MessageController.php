<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $user = auth()->user();
        $message = Message::create([
            'user_id' => $user ? $user->id : null,
            'sender' => $user ? $user->nom : 'Admin',
            'content' => $request->content,
            'sent_at' => now(),
        ]);

        return response()->json(['status' => 'sent', 'message' => $message]);
    }
} 