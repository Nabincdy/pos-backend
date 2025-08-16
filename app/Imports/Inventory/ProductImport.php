<?php

namespace App\Imports\Inventory;

use App\Models\Inventory\Brand;
use App\Models\Inventory\Product;
use App\Models\Inventory\ProductCategory;
use App\Models\Inventory\Unit;
use App\Models\Setting\Tax;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithBatchInserts
{
    use Importable;

    public function __construct($request)
    {
    }

    public function model(array $row): Product
    {
        return new Product([
            'product_category_id' => ProductCategory::where('name', $row['product_category'])->first()->id ?? null,
            'name' => $row['name'],
            'code' => $row['code'],
            'sku' => $row['sku'] ?? null,
            'reorder_quantity' => $row['reorder_quantity'] ?? 0,
            'barcode' => $row['barcode'] ?? null,
            'unit_id' => Unit::where('name', $row['unit'])->first()->id ?? null,
            'brand_id' => Brand::where('name', $row['brand'])->first()->id ?? null,
            'purchase_rate' => $row['purchase_rate'] ?? 0,
            'purchase_tax_id' => Tax::where('name', $row['purchase_tax'])->first()->id ?? null,
            'sales_rate' => $row['sales_rate'] ?? 0,
            'sales_tax_id' => Tax::where('name', $row['sales_tax'])->first()->id ?? null,
            'description' => $row['description'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.product_category' => ['required', Rule::exists('product_categories', 'name')->withoutTrashed()],
            '*.name' => ['required'],
            '*.code' => ['required', Rule::unique('products', 'code')->withoutTrashed()],
            '*.sku' => ['nullable', Rule::unique('products', 'sku')->withoutTrashed()],
            '*.reorder_quantity' => ['required', 'integer'],
            '*.barcode' => ['nullable', Rule::unique('products', 'barcode')->withoutTrashed()],
            '*.unit' => ['required', Rule::exists('units', 'name')->withoutTrashed()],
            '*.brand' => ['nullable', Rule::exists('brands', 'name')->withoutTrashed()],
            '*.purchase_rate' => ['required', 'numeric'],
            '*.purchase_tax' => ['nullable', Rule::exists('taxes', 'name')->withoutTrashed()],
            '*.sales_rate' => ['required', 'numeric'],
            '*.sales_tax' => ['nullable', Rule::exists('taxes', 'name')->withoutTrashed()],
            '*.description' => ['nullable'],
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
