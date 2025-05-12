<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    
public function show()
{
    $user = Auth::user();
    $user->photo_url = $user->photo ? url('storage/' . $user->photo) : null;

    return response()->json([
        'user' => $user
    ]);
}

    
}
