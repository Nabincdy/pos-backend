<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'effective_from' => $this->effective_from ?? '',
            'salaryStructures' => SalaryStructureResource::collection($this->salaryStructures),
        ];
    }
}
