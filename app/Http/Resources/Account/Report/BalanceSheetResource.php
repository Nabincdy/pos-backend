<?php

namespace App\Http\Resources\Account\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class BalanceSheetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'group_name' => $this->group_name ?? '',
            'amount' => $this->whenLoaded('ledgers', function () {
                return abs($this->ledgers->sum('sum_dr_amount') - $this->ledgers->sum('sum_cr_amount'));
            }),
            'subGroups' => BalanceSheetResource::collection($this->whenLoaded('ledgerGroups')),
        ];
    }
}
