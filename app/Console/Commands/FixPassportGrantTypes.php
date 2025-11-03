<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;

class FixPassportGrantTypes extends Command
{
    protected $signature = 'passport:fix-grant-types';
    protected $description = 'Corriger les grant_types doublement encodés dans la table oauth_clients';

    public function handle()
    {
        $this->info('Correction des grant_types dans la table oauth_clients...');

        $clients = DB::table('oauth_clients')->get();

        foreach ($clients as $client) {
            $grantTypes = $client->grant_types;

            // Si c'est une chaîne, la décoder
            if (is_string($grantTypes)) {
                // Retirer les guillemets supplémentaires et décoder
                $grantTypes = trim($grantTypes, '"');
                $decoded = json_decode($grantTypes, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    DB::table('oauth_clients')
                        ->where('id', $client->id)
                        ->update(['grant_types' => json_encode($decoded)]);

                    $this->info("Client corrigé ID: {$client->id}");
                }
            }
        }

        $this->info('Terminé ! Les grant types ont été corrigés.');

        return 0;
    }
}