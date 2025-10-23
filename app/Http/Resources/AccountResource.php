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
            'account_number' => $this->account_number,
            'type' => $this->type,
            'balance' => $this->balance,
            'status' => $this->status,
            'client' => [
                'id' => $this->client->id,
                'user' => [
                    'id' => $this->client->user->id,
                    'name' => $this->client->user->name,
                    'email' => $this->client->user->email,
                ],
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}