<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RefreshDatabaseForDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:refresh-db {--seed : Exécuter les seeders après la migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rafraîchir la base de données pour le déploiement avec données de test propres';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Début du rafraîchissement de la base de données pour le déploiement...');

        // Confirmation
        if (!$this->confirm('⚠️  Cette action va supprimer toutes les données existantes. Continuer ?')) {
            $this->info('❌ Opération annulée.');
            return;
        }

        // Étape 1: Migration fraîche
        $this->info('📦 Exécution des migrations...');
        Artisan::call('migrate:fresh', [], $this->getOutput());
        $this->info('✅ Migrations exécutées avec succès.');

        // Étape 2: Seeders (optionnel)
        if ($this->option('seed')) {
            $this->info('🌱 Exécution des seeders...');
            Artisan::call('db:seed', [], $this->getOutput());
            $this->info('✅ Seeders exécutés avec succès.');
        }

        // Étape 3: Régénération de la documentation Swagger
        $this->info('📚 Régénération de la documentation Swagger...');
        Artisan::call('l5-swagger:generate', [], $this->getOutput());
        $this->info('✅ Documentation Swagger régénérée.');

        // Étape 4: Clear cache
        $this->info('🧹 Nettoyage du cache...');
        Artisan::call('cache:clear', [], $this->getOutput());
        Artisan::call('config:clear', [], $this->getOutput());
        Artisan::call('route:clear', [], $this->getOutput());
        $this->info('✅ Cache nettoyé.');

        $this->info('🎉 Rafraîchissement terminé ! La base de données est prête pour les tests.');
        $this->info('💡 Pensez à régénérer la documentation Swagger après chaque déploiement : php artisan l5-swagger:generate');

        return Command::SUCCESS;
    }
}
