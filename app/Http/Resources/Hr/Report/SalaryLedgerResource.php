<?php

namespace App\Http\Resources\Hr\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryLedgerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'date' => $this->date ?? '',
            'month' => $this->month->name ?? '',
            'particular' => $this->remarks ?? '',
            'dr_amount' => $this->dr_amount ?? 0,
            'cr_amount' => $this->cr_amount ?? 0,
        ];
    }
}
