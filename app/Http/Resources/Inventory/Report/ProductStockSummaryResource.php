<?php

namespace App\Http\Resources\Inventory\Report;

use App\Models\Inventory\ProductOpening;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseReturn;
use App\Models\Inventory\Sale;
use App\Models\Inventory\SalesReturn;
use App\Models\Inventory\StockAdjustment;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStockSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product_name' => $this->name ?? '',
            'opening_quantity' => $this->productStocks->where('model_type', ProductOpening::class)->sum('quantity'),
            'purchase_quantity' => $this->productStocks->where('model_type', Purchase::class)->sum('quantity'),
            'purchase_return_quantity' => $this->productStocks->where('model_type', PurchaseReturn::class)->sum('quantity'),
            'sales_quantity' => $this->productStocks->where('model_type', Sale::class)->sum('quantity'),
            'sales_return_quantity' => $this->productStocks->where('model_type', SalesReturn::class)->sum('quantity'),
            'stock_adjustment_quantity' => $this->productStocks->where('model_type', StockAdjustment::class)->sum('quantity'),
            'stock_quantity' => $this->productStocks->sum('quantity'),
            'total_amount' => $this->productStocks->sum('amount'),
        ];
    }
}
