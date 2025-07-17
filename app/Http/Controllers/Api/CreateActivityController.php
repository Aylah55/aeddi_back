<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CreateActivityController extends Controller
{
    public function __invoke(Request $request)
    {
        // Valider les données
        $validator = Validator::make($request->all(), Activite::$rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Formater les dates pour la base de données
            $data = $request->only(['nom', 'description', 'status']);
            $data['date_debut'] = Carbon::parse($request->date_debut)->format('Y-m-d H:i:s');
            $data['date_fin'] = Carbon::parse($request->date_fin)->format('Y-m-d H:i:s');
            
            // Créer l'activité
            $activite = Activite::create($data);

            // Notifier tous les utilisateurs non-admin de l'ajout
            $users = \App\Models\User::where('role', '!=', 'admin')->get();
            $admin = $request->user();
            $adminName = $admin ? ($admin->prenom . ' ' . $admin->nom) : 'Administrateur';
            foreach ($users as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'admin_id' => $admin ? $admin->id : null,
                    'title' => $activite->nom,
                    'message' => $adminName . ' a ajouté une nouvelle activité : « ' . $activite->nom . ' »',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Activité créée avec succès',
                'data' => $activite
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Erreur création activité: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'activité',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
