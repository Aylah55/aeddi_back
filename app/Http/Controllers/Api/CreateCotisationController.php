<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CreateCotisationController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'montant' => 'required|numeric|min:0',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after:date_debut',
                'status' => 'required|string|in:À payer,En cours,Payé'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cotisation = Cotisation::create($request->all());

            // Récupérer tous les utilisateurs non-admin
            $users = User::where('role', '!=', 'admin')->get();

            // Attacher la cotisation à tous les utilisateurs
            foreach ($users as $user) {
                $user->cotisations()->attach($cotisation->id, [
                    'statut_paiement' => 'Non payé'
                ]);
            }

            return response()->json([
                'message' => 'Cotisation créée avec succès',
                'data' => $cotisation
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création de la cotisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 