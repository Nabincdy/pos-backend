<?php

namespace App\Http\Requests\Api\Inventory\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('product_edit');
    }

    public function rules(): array
    {
        return [
            'product_category_id' => ['required', Rule::exists('product_categories', 'id')->withoutTrashed()],
            'name' => ['required', Rule::unique('products', 'name')->withoutTrashed()->ignore($this->product)],
            'code' => ['required', Rule::unique('products', 'code')->withoutTrashed()->ignore($this->product)],
            'sku' => ['nullable', Rule::unique('products', 'sku')->withoutTrashed()->ignore($this->product)],
            'product_type' => ['nullable'],
            'reorder_quantity' => ['required', 'integer'],
            'barcode' => ['nullable', Rule::unique('products', 'barcode')->withoutTrashed()->ignore($this->product)],
            'unit_id' => ['required', Rule::exists('units', 'id')->withoutTrashed()],
            'brand_id' => ['nullable', Rule::exists('brands', 'id')->withoutTrashed()],
            'purchase_rate' => ['required', 'numeric'],
            'purchase_tax_id' => ['nullable', Rule::exists('taxes', 'id')->withoutTrashed()],
            'sales_rate' => ['required', 'numeric'],
            'sales_tax_id' => ['nullable', Rule::exists('taxes', 'id')->withoutTrashed()],
            'image' => ['nullable', 'image'],
            'description' => ['nullable'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
