<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductOpeningResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'rate' => $this->rate ?? '',
            'quantity' => $this->quantity ?? '',
            'product_name' => $this->whenLoaded('product', function () {
                return $this->product->name ?? '';
            }),
            'warehouse_name' => $this->whenLoaded('warehouse', function () {
                return $this->warehouse->name ?? '';
            }),
            'product_id' => $this->product_id ?? '',
            'warehouse_id' => $this->warehouse_id ?? '',
            'unit_id' => $this->unit_id ?? '',
            'opening_date' => $this->opening_date ?? '',
            'en_opening_date' => $this->en_opening_date ?? '',
            'batch_no' => $this->batch_no ?? '',
            'expiry_date' => $this->expiry_date ?? '',
            'en_expiry_date' => $this->en_expiry_date ?? '',
            'remarks' => $this->remarks ?? '',
        ];
    }
}
