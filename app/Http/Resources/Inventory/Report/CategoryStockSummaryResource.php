<?php

namespace App\Http\Resources\Inventory\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryStockSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'category_name' => $this->name ?? '',
            'opening_quantity' => $this->products->sum('opening_quantity_sum'),
            'purchase_quantity' => $this->products->sum('purchase_quantity_sum'),
            'purchase_return_quantity' => $this->products->sum('purchase_return_quantity_sum'),
            'sales_quantity' => $this->products->sum('sales_quantity_sum'),
            'sales_return_quantity' => $this->products->sum('sales_return_quantity_sum'),
            'stock_adjustment_quantity' => $this->products->sum('stock_adjustment_quantity_sum'),
            'stock_quantity' => $this->products->sum('stock_quantity_sum'),
            'total_amount' => $this->products->sum('amount_sum'),
            'sub_categories' => CategoryStockSummaryResource::collection($this->whenLoaded('productCategories')),
        ];
    }
}
