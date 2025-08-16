<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class StockAdjustmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'reference_no' => $this->reference_no ?? '',
            'adjustment_date' => $this->adjustment_date ?? '',
            'remarks' => $this->remarks ?? '',
            'created_by' => $this->whenLoaded('createUser', function () {
                return $this->createUser->name ?? '';
            }),
            'productStocks' => ProductStockResource::collection($this->whenLoaded('productStocks')),
        ];
    }
}
