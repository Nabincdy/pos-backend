<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class PayHeadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'name' => $this->name ?? '',
            'ledger_id' => $this->ledger_id ?? '',
            'type' => $this->type ?? '',
            'is_taxable' => $this->is_taxable ?? false,
            'tax_id' => $this->tax_id ?? '',
            'tax' => $this->whenLoaded('tax', function () {
                return $this->tax->name ?? '';
            }),
        ];
    }
}
