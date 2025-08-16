<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'ledger_id' => $this->ledger_id ?? '',
            'name' => $this->name ?? '',
            'code' => $this->code ?? '',
            'rate' => $this->rate ?? 0,
        ];
    }
}
