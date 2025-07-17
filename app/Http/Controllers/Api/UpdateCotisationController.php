<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cotisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateCotisationController extends Controller
{
    public function __invoke(Request $request, $id)
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

            $cotisation = Cotisation::findOrFail($id);
            $cotisation->update($request->all());

            // Notifier tous les utilisateurs non-admin de la modification
            $users = \App\Models\User::where('role', '!=', 'admin')->get();
            $admin = $request->user();
            $adminName = $admin ? ($admin->prenom . ' ' . $admin->nom) : 'Administrateur';
            foreach ($users as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'admin_id' => $admin ? $admin->id : null,
                    'title' => $cotisation->nom,
                    'message' => $adminName . ' a modifié la cotisation : « ' . $cotisation->nom . ' »',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return response()->json([
                'message' => 'Cotisation mise à jour avec succès',
                'data' => $cotisation
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cotisation non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de la cotisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 