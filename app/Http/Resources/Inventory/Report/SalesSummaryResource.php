<?php

namespace App\Http\Resources\Inventory\Report;

use App\Http\Resources\Inventory\SaleParticularResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'invoice_no' => $this->invoice_no ?? '',
            'sales_date' => $this->sales_date ?? '',
            'client' => $this->clientLedger->ledger_name ?? '',
            'remarks' => $this->remarks ?? '',
            'total_quantity' => $this->saleParticulars->sum('quantity'),
            'total_amount' => round($this->saleParticulars->sum('total_amount'), 2),
            'sub_total_amount' => round($this->saleParticulars->sum('sub_total'), 2),
            'tax_amount' => round($this->saleParticulars->sum('sales_tax_amount'), 2),
            'discount_amount' => round($this->saleParticulars->sum('discount_amount'), 2),
            'paid_amount' => round($this->receipt_records_sum_amount ?? 0, 2),
            'due_amount' => round((($this->saleParticulars->sum('total_amount')) - ($this->receipt_records_sum_amount ?? 0)), 2),
            'saleParticulars' => SaleParticularResource::collection($this->whenLoaded('saleParticulars')),
        ];
    }
}
