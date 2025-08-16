<?php

namespace App\Http\Resources\Hr;

use Illuminate\Http\Resources\Json\JsonResource;

class SalaryStructureResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'pay_head_id' => $this->pay_head_id ?? '',
            'pay_head' => $this->payHead->name ?? '',
            'type' => $this->payHead->type ?? '',
            'amount' => $this->amount ?? 0,
        ];
    }
}
