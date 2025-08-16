<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Resources\Json\JsonResource;

class LedgerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'ledger_name' => $this->ledger_name ?? '',
            'code' => $this->code ?? '',
            'category' => $this->category ?? '',
            'address' => $this->address ?? '',
            'phone' => $this->phone ?? '',
            'status' => $this->status ?? true,
            'email' => $this->email ?? '',
            'pan_no' => $this->pan_no ?? '',
            $this->mergeWhen(request()->routeIs('admin.account.ledger.show'), [
                'ledger_id' => $this->ledger_id ?? '',
                'ledger_group_id' => $this->ledger_group_id ?? '',
            ]),
            'ledger_group' => $this->whenLoaded('ledgerGroup', function () {
                return $this->ledgerGroup->group_name ?? '';
            }),
            'sub_ledgers' => LedgerResource::collection($this->whenLoaded('subLedgers')),
            'auto_generated' => $this->auto_generated ?? false,
        ];
    }
}
