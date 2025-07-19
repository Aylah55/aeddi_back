<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestUserDeletion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-deletion {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test la suppression d\'un utilisateur et de toutes ses données associées';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Test de suppression pour l'utilisateur: {$email}");
        
        // Trouver l'utilisateur
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Utilisateur non trouvé avec l'email: {$email}");
            return 1;
        }
        
        $this->info("Utilisateur trouvé: ID {$user->id}, Nom: {$user->nom} {$user->prenom}");
        
        // Compter les données associées
        $notificationsCount = $user->notifications()->count();
        $messagesCount = $user->messages()->count();
        $cotisationsCount = $user->cotisations()->count();
        
        $this->info("Données associées trouvées:");
        $this->info("- Notifications: {$notificationsCount}");
        $this->info("- Messages: {$messagesCount}");
        $this->info("- Cotisations: {$cotisationsCount}");
        
        // Demander confirmation
        if (!$this->confirm("Voulez-vous vraiment supprimer cet utilisateur et toutes ses données ?")) {
            $this->info("Suppression annulée.");
            return 0;
        }
        
        try {
            DB::beginTransaction();
            
            // Supprimer l'utilisateur (le modèle User::boot() s'occupera du reste)
            $user->delete();
            
            DB::commit();
            
            $this->info("✅ Utilisateur et toutes ses données supprimés avec succès !");
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Erreur lors de la suppression: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
