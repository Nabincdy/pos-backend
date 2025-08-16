<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvanceSalaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'employee_name' => $this->employee->name ?? '',
            'employee_id' => $this->employee_id ?? '',
            'date' => $this->date ?? '',
            'amount' => $this->amount ?? 0,
            'deduct_month_id' => $this->deduct_month_id ?? '',
            'deduct_month' => $this->deductMonth->name ?? '',
            'remarks' => $this->remarks ?? '',
        ];
    }
}
