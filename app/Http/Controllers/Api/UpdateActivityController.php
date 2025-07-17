<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateActivityController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after:date_debut',
                'status' => 'required|string|in:À venir,En cours,Terminé'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupérer l'activité
            $activite = Activite::findOrFail($id);

            // Mettre à jour l'activité
            $activite->update([
                'nom' => $request->nom,
                'description' => $request->description,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'status' => $request->status
            ]);

            // Notifier tous les utilisateurs non-admin de la modification
            $users = \App\Models\User::where('role', '!=', 'admin')->get();
            $admin = $request->user();
            $adminName = $admin ? ($admin->prenom . ' ' . $admin->nom) : 'Administrateur';
            foreach ($users as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'admin_id' => $admin ? $admin->id : null,
                    'title' => $activite->nom,
                    'message' => $adminName . ' a modifié l\'activité : « ' . $activite->nom . ' »',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return response()->json([
                'message' => 'Activité mise à jour avec succès',
                'data' => $activite
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Activité non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la mise à jour de l\'activité',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}