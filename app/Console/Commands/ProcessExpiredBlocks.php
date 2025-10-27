<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessExpiredBlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:process-expired-blocks {--dry-run : Exécuter en mode test sans archiver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traite les comptes dont le blocage a expiré et les archive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 Mode test activé - Aucune modification ne sera effectuée');
        }

        $this->info('🚀 Démarrage du traitement des blocages de comptes expirés...');

        // Récupérer tous les comptes dont le blocage a expiré
        $expiredAccounts = \App\Models\Account::expiredBlocking()
            ->notArchived()
            ->with(['client.user', 'transactions'])
            ->get();

        if ($expiredAccounts->isEmpty()) {
            $this->info('✅ Aucun compte avec blocage expiré trouvé');
            return;
        }

        $this->info("📊 {$expiredAccounts->count()} compte(s) avec blocage expiré trouvé(s)");

        $progressBar = $this->output->createProgressBar($expiredAccounts->count());
        $progressBar->start();

        $processed = 0;
        $archived = 0;

        foreach ($expiredAccounts as $account) {
            try {
                if (!$dryRun) {
                    // Archiver le compte via le Job
                    \App\Jobs\ProcessExpiredAccountBlocks::dispatch($account);
                    $archived++;
                }

                $this->newLine();
                $this->info("✅ Compte {$account->account_number} programmé pour archivage");

                $processed++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Erreur avec le compte {$account->account_number}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("🔍 Test terminé - {$processed} compte(s) seraient archivés");
        } else {
            $this->info("✅ Traitement terminé - {$archived} compte(s) programmés pour archivage");
        }

        $this->info('📝 Les comptes seront archivés dans la base de données Neon serverless');
    }
}
