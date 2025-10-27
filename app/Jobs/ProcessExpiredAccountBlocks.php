<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessExpiredAccountBlocks implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Démarrage du traitement des blocages de comptes expirés');

        // Récupérer tous les comptes dont le blocage a expiré
        $expiredAccounts = Account::expiredBlocking()
            ->notArchived()
            ->with(['client.user', 'transactions'])
            ->get();

        Log::info("Nombre de comptes expirés trouvés: {$expiredAccounts->count()}");

        foreach ($expiredAccounts as $account) {
            DB::transaction(function () use ($account) {
                try {
                    // Archiver le compte et ses transactions
                    $this->archiveAccount($account);

                    Log::info("Compte archivé avec succès: {$account->account_number}");
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'archivage du compte {$account->account_number}: {$e->getMessage()}");
                    throw $e;
                }
            });
        }

        Log::info('Traitement des blocages expirés terminé');
    }

    /**
     * Archive un compte et ses transactions dans la base de données d'archivage
     */
    private function archiveAccount(Account $account): void
    {
        // Pour cette implémentation, nous simulons l'archivage
        // En production, cela devrait être fait dans une base de données séparée (Neon)

        // Marquer le compte comme archivé dans la base principale
        $account->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        // Simuler l'envoi vers la base d'archivage
        $archiveData = [
            'account' => $account->toArray(),
            'client' => $account->client->toArray(),
            'user' => $account->client->user->toArray(),
            'transactions' => $account->transactions->toArray(),
            'archived_at' => now(),
        ];

        // Log des données d'archivage (en production, envoyer vers Neon)
        Log::info('Données d\'archivage préparées', [
            'account_number' => $account->account_number,
            'transaction_count' => $account->transactions->count(),
            'archive_size' => strlen(json_encode($archiveData))
        ]);

        // Ici, en production, vous feriez :
        // 1. Connexion à la base Neon
        // 2. Insertion des données dans les tables d'archivage
        // 3. Suppression logique des données de la base principale si nécessaire
    }
}
