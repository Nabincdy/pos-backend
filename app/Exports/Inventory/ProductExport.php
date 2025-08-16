<?php

namespace App\Exports\Inventory;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements WithHeadings, FromCollection, WithMapping
{
    public function __construct(public Collection $products)
    {
    }

    public function headings(): array
    {
        return [
            'product_category',
            'name',
            'code',
            'sku',
            'reorder_quantity',
            'barcode',
            'unit',
            'brand',
            'purchase_rate',
            'purchase_tax',
            'sales_rate',
            'sales_tax',
            'description',
        ];
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function map($product): array
    {
        // Modify or map the collection data here
        return [
            $product->productCategory->name ?? '',
            $product->name,
            $product->code,
            $product->sku,
            $product->reorder_quantity,
            $product->barcode,
            $product->unit->name ?? '',
            $product->brand->name ?? '',
            $product->purchase_rate,
            $product->purchaseTax->name ?? '',
            $product->sales_rate,
            $product->salesTax->name ?? '',
            $product->description,
        ];
    }
}
