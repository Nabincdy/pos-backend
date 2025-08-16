<?php

namespace App\Http\Resources\Account\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class TrialBalanceParticularResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'ledger_name' => $this->ledger_name ?? '',
            'dr_amount' => $this->sumDrAmount(),
            'cr_amount' => $this->sumCrAmount(),
            'sub_ledgers' => TrialBalanceParticularResource::collection($this->whenLoaded('subLedgers')),
            'opening_balance_dr' => $this->openingBalance() > 0 ? round($this->openingBalance(), 2) : 0,
            'opening_balance_cr' => $this->openingBalance() < 0 ? abs(round($this->openingBalance(), 2)) : 0,
            'closing_balance_dr' => $this->closingBalance() > 0 ? round($this->closingBalance(), 2) : 0,
            'closing_balance_cr' => $this->closingBalance() < 0 ? abs(round($this->closingBalance(), 2)) : 0,
        ];
    }

    private function sumDrAmount(): float|int
    {
        return round($this->sum_dr_amount ?? 0, 2) + ($this->relationLoaded('subLedgers') ? $this->subLedgers->sum('sum_dr_amount') : 0);
    }

    private function sumCrAmount(): float|int
    {
        return round($this->sum_cr_amount ?? 0, 2) + ($this->relationLoaded('subLedgers') ? $this->subLedgers->sum('sum_cr_amount') : 0);
    }

    protected function openingBalance()
    {
        return ($this->sum_opening_dr_amount - $this->sum_opening_cr_amount) + ($this->relationLoaded('subLedgers') ? ($this->subLedgers->sum('sum_opening_dr_amount') - $this->subLedgers->sum('sum_opening_cr_amount')) : 0);
    }

    protected function closingBalance()
    {
        return $this->sumDrAmount() - $this->sumCrAmount() + $this->openingBalance();
    }
}
