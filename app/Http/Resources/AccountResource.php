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
        return [
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
    }
}