<?php

namespace App\Http\Resources\Account\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class TrialBalanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'group_name' => $this->group_name ?? '',
            $this->whenLoaded('ledgers', function () {
                return $this->merge([
                    'dr_amount' => $this->sumDrAmount(),
                    'cr_amount' => $this->sumCrAmount(),
                    'opening_balance_dr' => $this->openingBalance() > 0 ? round($this->openingBalance(), 2) : 0,
                    'opening_balance_cr' => $this->openingBalance() < 0 ? abs(round($this->openingBalance(), 2)) : 0,
                    'closing_balance_dr' => $this->closingBalance() > 0 ? round($this->closingBalance(), 2) : 0,
                    'closing_balance_cr' => $this->closingBalance() < 0 ? abs(round($this->closingBalance(), 2)) : 0,
                ]);
            }),
            'subGroups' => TrialBalanceResource::collection($this->whenLoaded('ledgerGroups')),
            'ledgers' => TrialBalanceParticularResource::collection($this->whenLoaded('ledgers')),
        ];
    }

    private function sumDrAmount(): float
    {
        $dr_amount = round($this->ledgers->sum('sum_dr_amount'), 2);

        foreach ($this->ledgers as $ledger) {
            $dr_amount += $ledger->subLedgers->sum('sum_dr_amount');
        }

        return round($dr_amount, 2);
    }

    private function sumCrAmount(): float
    {
        $cr_amount = round($this->ledgers->sum('sum_cr_amount'), 2);

        foreach ($this->ledgers as $ledger) {
            $cr_amount += $ledger->subLedgers->sum('sum_cr_amount');
        }

        return round($cr_amount, 2);
    }

    public function sumOpeningDrAmount(): float
    {
        $sum_opening_dr = round($this->ledgers->sum('sum_opening_dr_amount'), 2);
        foreach ($this->ledgers as $ledger) {
            $sum_opening_dr += $ledger->subLedgers->sum('sum_opening_dr_amount');
        }

        return round($sum_opening_dr, 2);
    }

    public function sumOpeningCrAmount(): float
    {
        $sum_opening_cr = round($this->ledgers->sum('sum_opening_cr_amount'), 2);
        foreach ($this->ledgers as $ledger) {
            $sum_opening_cr += $ledger->subLedgers->sum('sum_opening_cr_amount');
        }

        return round($sum_opening_cr, 2);
    }

    protected function openingBalance(): float
    {
        return $this->sumOpeningDrAmount() - $this->sumOpeningCrAmount();
    }

    protected function closingBalance(): float
    {
        return $this->sumDrAmount() - $this->sumCrAmount() + $this->openingBalance();
    }
}
