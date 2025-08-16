<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'warehouse_id' => $this->warehouse_id ?? '',
            'name' => $this->name ?? '',
            'code' => $this->code ?? '',
            'phone' => $this->phone ?? '',
            'address' => $this->address ?? '',
            'stock_quantity' => (int) ($this->product_stocks_sum_quantity + ($this->relationLoaded('warehouses') ? $this->warehouses?->sum('product_stocks_sum_quantity') : 0)),
            'sub_warehouses' => WarehouseResource::collection($this->whenLoaded('warehouses')),
        ];
    }
}
