<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'product_category_id' => $this->product_category_id ?? '',
            'product_category' => $this->whenLoaded('productCategory', function () {
                return $this->productCategory->name ?? '';
            }),
            'name' => $this->name ?? '',
            'code' => $this->code ?? '',
            'sku' => $this->sku ?? '',
            'stock_quantity' => (int) ($this->product_stocks_sum_quantity ?? 0),
            'product_type' => $this->product_type ?? '',
            'reorder_quantity' => $this->reorder_quantity ?? 0,
            'barcode' => $this->barcode ?? '',
            'unit_id' => $this->unit_id ?? '',
            'unit' => $this->whenLoaded('unit', function () {
                return $this->unit->name ?? '';
            }),
            'brand_id' => $this->brand_id ?? '',
            'brand_name' => $this->whenLoaded('brand', function () {
                return $this->brand->name ?? '';
            }),
            'purchase_rate' => $this->purchase_rate ?? 0,
            'purchase_tax_id' => $this->purchase_tax_id ?? '',
            'sales_rate' => $this->sales_rate ?? 0,
            'sales_tax_id' => $this->sales_tax_id ?? '',
            'image_url' => $this->image_url ?? '',
            'description' => $this->description ?? '',
            'status' => $this->status ?? false,
        ];
    }
}
