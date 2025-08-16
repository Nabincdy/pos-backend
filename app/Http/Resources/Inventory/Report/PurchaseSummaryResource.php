<?php

namespace App\Http\Resources\Inventory\Report;

use App\Http\Resources\Inventory\PurchaseParticularResource;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'invoice_no' => $this->invoice_no ?? '',
            'purchase_date' => $this->purchase_date ?? '',
            'supplier' => $this->supplierLedger->ledger_name ?? '',
            'remarks' => $this->remarks ?? '',
            'total_quantity' => $this->purchaseParticulars->sum('quantity'),
            'total_amount' => round($this->purchaseParticulars->sum('total_amount'), 2),
            'sub_total_amount' => round($this->purchaseParticulars->sum('sub_total'), 2),
            'tax_amount' => round($this->purchaseParticulars->sum('purchase_tax_amount'), 2),
            'discount_amount' => round($this->purchaseParticulars->sum('discount_amount'), 2),
            'paid_amount' => round($this->payment_records_sum_paid_amount ?? 0, 2),
            'due_amount' => round((($this->purchaseParticulars->sum('total_amount')) - ($this->payment_records_sum_paid_amount ?? 0)), 2),
            'purchaseParticulars' => PurchaseParticularResource::collection($this->whenLoaded('purchaseParticulars')),
        ];
    }
}
