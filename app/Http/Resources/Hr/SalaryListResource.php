<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'employee_id' => $this->id ?? '',
            'employee_name' => $this->name ?? '',
            'salaryStructures' => SalaryStructureResource::collection($this->latestSalary->salaryStructures ?? collect()),
            'salary_amount_sum' => $this->salaryAmountSum(),
        ];
    }

    private function salaryAmountSum()
    {
        $amount = 0;
        foreach ($this->latestSalary->salaryStructures ?? collect() as $salaryStructure) {
            if ($salaryStructure->payHead->type == 'Deduction') {
                $amount -= $salaryStructure->amount;
            } else {
                $amount += $salaryStructure->amount;
            }
        }

        return $amount;
    }
}
