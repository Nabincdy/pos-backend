<?php

namespace App\Http\Resources\Inventory;

use App\Http\Resources\Account\LedgerResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use NumberFormatter;

class PaymentRecordResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'invoice_no' => $this->invoice_no ?? '',
            'cashBankLedger' => $this->whenLoaded('cashBankLedger', function () {
                return $this->cashBankLedger->ledger_name ?? '';
            }),
            'payment_date' => $this->payment_date ?? '',
            'supplier' => LedgerResource::make($this->whenLoaded('supplierLedger')),
            'paid_amount' => $this->paid_amount ?? '',
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'amount_in_words' => Str::headline($this->numberFormatter($this->paid_amount)),
            'remarks' => $this->remarks ?? '',
        ];
    }

    public function numberFormatter($digit): bool|string
    {
        $format = new \NumberFormatter('en', NumberFormatter::SPELLOUT);

        return $format->format($digit);
    }
}
