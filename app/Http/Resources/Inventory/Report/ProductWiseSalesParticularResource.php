<?php

namespace App\Http\Resources\Inventory\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductWiseSalesParticularResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'invoice_no' => $this->sale->invoice_no ?? '',
            'date' => $this->sale->sales_date ?? '',
            'client' => $this->sale->clientLedger->ledger_name ?? '',
            'quantity' => $this->quantity ?? 0,
            'rate' => $this->rate ?? 0,
            'sub_total' => $this->sub_total ?? 0,
            'discount_amount' => $this->discount_amount ?? 0,
            'tax_amount' => $this->sales_tax_amount ?? 0,
            'total_amount' => $this->total_amount ?? 0,
        ];
    }
}
