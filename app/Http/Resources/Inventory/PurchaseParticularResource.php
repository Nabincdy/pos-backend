<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseParticularResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'product_name' => $this->whenLoaded('product', function () {
                return $this->product->name ?? '';
            }),
            'unit' => $this->whenLoaded('unit', function () {
                return $this->unit->name ?? '';
            }),
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return $this->warehouse->name ?? '';
            }),
            'quantity' => $this->quantity ?? 0,
            'product_rate' => $this->product_rate ?? 0,
            'sub_total_amount' => $this->sub_total ?? 0,
            'batch_no' => $this->batch_no ?? '',
            'expiry_date' => $this->expiry_date ?? '',
            'discount_amount' => $this->discount_amount ?? 0,
            'purchase_tax_amount' => $this->purchase_tax_amount ?? 0,
            'amount_excl_tax' => $this->amount_excl_tax ?? 0,
            'total_amount' => $this->total_amount ?? 0,
            'is_cancelled' => $this->is_cancelled ?? false,
        ];
    }
}
