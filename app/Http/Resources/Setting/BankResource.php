<?php

namespace App\Http\Resources\Setting;

use App\Http\Resources\Account\BankAccountResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'ledger_id' => $this->ledger_id ?? '',
            'name' => $this->name ?? '',
            'code' => $this->code ?? '',
            'bankAccounts' => BankAccountResource::collection($this->whenLoaded('bankAccounts')),
        ];
    }
}
