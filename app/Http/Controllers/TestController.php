<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'API Laravel fonctionne parfaitement !'
        ]);
    }
}
