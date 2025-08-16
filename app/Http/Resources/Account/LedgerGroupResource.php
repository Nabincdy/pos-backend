<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class LedgerGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'group_name' => $this->group_name ?? '',
            'code' => $this->code ?? '',
            'account_head' => $this->whenLoaded('accountHead', function () {
                return $this->accountHead->name ?? '';
            }),
            'ledger_group_id' => $this->ledger_group_id ?? '',
            'main_group' => $this->whenLoaded('ledgerGroup', function () {
                return $this->ledgerGroup->group_name ?? '';
            }),
            'auto_generated' => $this->auto_generated ?? false,
        ];
    }
}
