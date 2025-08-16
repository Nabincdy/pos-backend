<?php

namespace App\Http\Requests\Api\Inventory\StockAdjustment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('stockAdjustment_create');
    }

    public function rules(): array
    {
        return [
            'reference_no' => ['required', Rule::unique('stock_adjustments', 'reference_no')->withoutTrashed()],
            'adjustment_date' => ['required'],
            'productStocks' => ['required', 'array'],
            'productStocks.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->withoutTrashed()],
            'productStocks.*.unit_id' => ['required', Rule::exists('units', 'id')->withoutTrashed()],
            'productStocks.*.warehouse_id' => ['required', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'productStocks.*.type' => ['required', 'in:In,Out'],
            'productStocks.*.quantity' => ['required', 'numeric'],
            'productStocks.*.rate' => ['required', 'numeric'],
            'remarks' => ['nullable'],
        ];
    }
}
