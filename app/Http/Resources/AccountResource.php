<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'numeroCompte' => $this->account_number,
            'titulaire' => $this->client->user->name,
            'type' => $this->type,
            'solde' => $this->balance,
            'devise' => 'FCFA',
            'dateCreation' => $this->created_at->format('Y-m-d\TH:i:s\Z'),
            'statut' => $this->status === 'active' ? 'actif' : ($this->status === 'inactive' ? 'bloque' : 'ferme'),
            'metadata' => [
                'derniereModification' => $this->updated_at->format('Y-m-d\TH:i:s\Z'),
                'version' => 1,
            ],
        ];

        // Ajouter les informations de blocage si le compte est bloqué
        if ($this->status === 'inactive' && $this->blocked_at) {
            $data['informationsBlocage'] = [
                'dateDebutBlocage' => $this->blocked_at instanceof \Carbon\Carbon ? $this->blocked_at->format('Y-m-d\TH:i:s\Z') : $this->blocked_at,
                'dateFinBlocage' => $this->blocking_expires_at instanceof \Carbon\Carbon ? $this->blocking_expires_at->format('Y-m-d\TH:i:s\Z') : $this->blocking_expires_at,
                'motifBlocage' => $this->blocking_reason,
            ];
        }

        return $data;
    }
}