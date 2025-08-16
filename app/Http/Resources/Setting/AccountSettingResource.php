<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountSettingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'cash_ledger_id' => $this->cash_ledger_id ?? '',
            'bank_ledger_group_id' => $this->bank_ledger_group_id ?? '',
            'supplier_ledger_group_id' => $this->supplier_ledger_group_id ?? '',
            'client_ledger_group_id' => $this->client_ledger_group_id ?? '',
            'tax_ledger_group_id' => $this->tax_ledger_group_id ?? '',
            'purchase_ledger_id' => $this->purchase_ledger_id ?? '',
            'sales_ledger_id' => $this->sales_ledger_id ?? '',
            'advance_salary_id' => $this->advance_salary_id ?? '',
            'salary_payable_ledger_id' => $this->salary_payable_ledger_id ?? '',
        ];
    }
}
