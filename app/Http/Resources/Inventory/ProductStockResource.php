<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductStockResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'product_id' => $this->product_id ?? '',
            'product_name' => $this->whenLoaded('product', function () {
                return $this->product->name ?? '';
            }),
            'expiry_date' => $this->expiry_date ?? '',
            'en_expiry_date' => $this->en_expiry_date ?? '',
            'batch_no' => $this->batch_no ?? '',
            'unit_id' => $this->unit_id ?? '',
            'unit' => $this->whenLoaded('unit', function () {
                return $this->unit->name ?? '';
            }),
            'warehouse_id' => $this->warehouse_id ?? '',
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return $this->warehouse->name ?? '';
            }),
            'quantity' => $this->quantity ?? 0,
            'rate' => $this->rate ?? 0,
            'amount' => $this->amount ?? 0,
            'type' => $this->type ?? '',
            'date' => $this->date ?? '',
            'remarks' => $this->remarks ?? '',
        ];
    }
}
