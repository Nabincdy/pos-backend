<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountOpeningBalanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'ledger_id' => $this->ledger_id ?? '',
            'ledger' => $this->whenLoaded('ledger', function () {
                return $this->ledger->ledger_name ?? '';
            }),
            'amount' => $this->dr_amount > 0 ? $this->dr_amount : $this->cr_amount,
            'dr_amount' => $this->dr_amount ?? '',
            'cr_amount' => $this->cr_amount ?? '',
            'opening_date' => $this->opening_date ?? '',
            'remarks' => $this->remarks ?? '',
        ];
    }
}
