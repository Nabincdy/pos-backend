<?php

namespace App\Http\Resources\Inventory\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductWisePurchaseParticularResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'invoice_no' => $this->purchase->invoice_no ?? '',
            'date' => $this->purchase->purchase_date ?? '',
            'supplier' => $this->purchase->supplierLedger->ledger_name ?? '',
            'quantity' => $this->quantity ?? 0,
            'rate' => $this->product_rate ?? 0,
            'sub_total' => $this->sub_total ?? 0,
            'discount_amount' => $this->discount_amount ?? 0,
            'tax_amount' => $this->purchase_tax_amount ?? 0,
            'total_amount' => $this->total_amount ?? 0,
        ];
    }
}
