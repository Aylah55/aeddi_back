<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;

class TestNotificationCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester la création de notifications et vérifier l\'attachement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Test de création de notifications...');

        // Récupérer un admin et un utilisateur
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', '!=', 'admin')->first();

        if (!$admin) {
            $this->error('Aucun admin trouvé');
            return;
        }

        if (!$user) {
            $this->error('Aucun utilisateur trouvé');
            return;
        }

        $this->info("Admin: {$admin->prenom} {$admin->nom}");
        $this->info("Utilisateur: {$user->prenom} {$user->nom}");

        // Test 1: Créer une notification pour un utilisateur spécifique
        $this->info("\n1. Test création notification pour un utilisateur spécifique...");
        $notification1 = Notification::createForUser([
            'admin_id' => $admin->id,
            'title' => 'Test Notification Directe',
            'message' => 'Ceci est un test de notification directe',
            'type' => 'info'
        ], $user->id);

        $this->info("Notification créée avec ID: {$notification1->id}");
        $this->info("user_id: {$notification1->user_id}");

        // Vérifier l'attachement
        $pivotCount = \DB::table('notification_user')
            ->where('notification_id', $notification1->id)
            ->count();
        $this->info("Entrées dans notification_user: {$pivotCount}");

        // Test 2: Créer une notification pour tous les utilisateurs
        $this->info("\n2. Test création notification pour tous les utilisateurs...");
        $notification2 = Notification::createForAllUsers([
            'admin_id' => $admin->id,
            'title' => 'Test Notification Globale',
            'message' => 'Ceci est un test de notification globale',
            'type' => 'success'
        ]);

        $this->info("Notification créée avec ID: {$notification2->id}");

        // Vérifier l'attachement
        $pivotCount2 = \DB::table('notification_user')
            ->where('notification_id', $notification2->id)
            ->count();
        $this->info("Entrées dans notification_user: {$pivotCount2}");

        // Test 3: Créer une notification directement avec user_id
        $this->info("\n3. Test création notification directe avec user_id...");
        $notification3 = Notification::create([
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'title' => 'Test Notification Directe 2',
            'message' => 'Ceci est un test de notification directe via create()',
            'type' => 'warning'
        ]);

        $this->info("Notification créée avec ID: {$notification3->id}");
        $this->info("user_id: {$notification3->user_id}");

        // Vérifier l'attachement
        $pivotCount3 = \DB::table('notification_user')
            ->where('notification_id', $notification3->id)
            ->count();
        $this->info("Entrées dans notification_user: {$pivotCount3}");

        // Afficher le résumé
        $this->info("\n=== RÉSUMÉ ===");
        $totalNotifications = \DB::table('notifications')->count();
        $totalPivotEntries = \DB::table('notification_user')->count();
        $this->info("Total notifications: {$totalNotifications}");
        $this->info("Total entrées notification_user: {$totalPivotEntries}");

        if ($totalPivotEntries > 0) {
            $this->info("✅ L'attachement automatique fonctionne !");
        } else {
            $this->error("❌ L'attachement automatique ne fonctionne pas");
        }
    }
} 