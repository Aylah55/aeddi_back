<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;

class DistributeExistingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:distribute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribuer les notifications existantes aux utilisateurs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la distribution des notifications...');

        // Récupérer toutes les notifications existantes
        $notifications = Notification::all();
        $users = User::where('role', '!=', 'admin')->get();

        $this->info("Nombre de notifications à distribuer : " . $notifications->count());
        $this->info("Nombre d'utilisateurs : " . $users->count());

        if ($notifications->count() === 0) {
            $this->info('Aucune notification à distribuer.');
            return;
        }

        if ($users->count() === 0) {
            $this->error('Aucun utilisateur trouvé.');
            return;
        }

        $bar = $this->output->createProgressBar($notifications->count());
        $bar->start();

        $distributedCount = 0;
        $skippedCount = 0;

        foreach ($notifications as $notification) {
            // Vérifier si la notification est déjà distribuée
            $existingPivot = \DB::table('notification_user')
                ->where('notification_id', $notification->id)
                ->first();

            if (!$existingPivot) {
                // Si la notification a un user_id, l'attacher à cet utilisateur
                if ($notification->user_id) {
                    $notification->users()->attach($notification->user_id, [
                        'is_read' => false,
                        'created_at' => $notification->created_at,
                        'updated_at' => $notification->updated_at,
                    ]);
                    $distributedCount++;
                } else {
                    // Sinon, distribuer à tous les utilisateurs (comportement par défaut)
                    $notification->users()->attach($users->pluck('id')->toArray(), [
                        'is_read' => false,
                        'created_at' => $notification->created_at,
                        'updated_at' => $notification->updated_at,
                    ]);
                    $distributedCount++;
                }
            } else {
                $skippedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        $this->info("Distribution terminée !");
        $this->info("Notifications distribuées : {$distributedCount}");
        $this->info("Notifications déjà distribuées (ignorées) : {$skippedCount}");
        
        // Vérification finale
        $totalNotifications = \DB::table('notifications')->count();
        $totalPivotEntries = \DB::table('notification_user')->count();
        $this->info("Total notifications : {$totalNotifications}");
        $this->info("Total entrées notification_user : {$totalPivotEntries}");
        
        if ($totalPivotEntries > 0) {
            $this->info("✅ Distribution réussie !");
        } else {
            $this->error("❌ Aucune notification n'a été distribuée");
        }
    }
} 