<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use Illuminate\Http\Request;

class GetCotisationController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $cotisations = Cotisation::orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $cotisations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des cotisations'
            ], 500);
        }
    }
} 