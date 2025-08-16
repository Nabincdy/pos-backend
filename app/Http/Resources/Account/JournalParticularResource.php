<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class JournalParticularResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'journal_no' => $this->whenLoaded('journal', function () {
                return $this->journal->journal_no ?? '';
            }),
            'ledger_name' => $this->whenLoaded('ledger', function () {
                return $this->ledger->ledger_name ?? '';
            }),
            'date' => $this->date ?? '',
            'dr_amount' => $this->dr_amount ?? '',
            'cr_amount' => $this->cr_amount ?? '',
            'remarks' => $this->remarks ?? '',
        ];
    }
}
