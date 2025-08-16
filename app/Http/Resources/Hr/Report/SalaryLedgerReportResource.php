<?php

namespace App\Http\Resources\Hr\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryLedgerReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'employee_name' => $this->name ?? '',
            'dr_amount_sum' => (float) $this->salaryLedgers->sum('dr_amount'),
            'cr_amount_sum' => (float) $this->salaryLedgers->sum('cr_amount'),
            'total_balance' => $this->salaryLedgers->sum('dr_amount') - $this->salaryLedgers->sum('cr_amount'),
            'salaryLedgers' => SalaryLedgerResource::collection($this->salaryLedgers),
        ];
    }
}
