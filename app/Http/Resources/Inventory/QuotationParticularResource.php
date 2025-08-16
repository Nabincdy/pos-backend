<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class QuotationParticularResource extends JsonResource
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
            'rate' => $this->rate ?? 0,
            'sub_total_amount' => $this->sub_total ?? 0,
            'discount_amount' => $this->discount_amount ?? 0,
            'sales_tax_amount' => $this->sales_tax_amount ?? 0,
            'total_amount' => $this->total_amount ?? 0,
        ];
    }
}
