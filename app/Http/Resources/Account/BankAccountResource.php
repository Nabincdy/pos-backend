<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'bank_id' => $this->bank_id ?? '',
            'ledger_id' => $this->ledger_id ?? '',
            'bank_name' => $this->whenLoaded('bank', function () {
                return $this->bank->name ?? '';
            }),
            'branch' => $this->branch ?? '',
            'account_name' => $this->account_name ?? '',
            'account_no' => $this->account_no ?? '',
            'code' => $this->code ?? '',
        ];
    }
}
