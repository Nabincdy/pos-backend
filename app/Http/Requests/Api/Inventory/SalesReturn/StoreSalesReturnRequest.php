<?php

namespace App\Http\Requests\Api\Inventory\SalesReturn;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('salesReturn_create');
    }

    public function rules(): array
    {
        return [
            'invoice_no' => ['required', Rule::unique('sales_returns', 'invoice_no')->withoutTrashed()],
            'return_date' => ['required'],
            'client_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'salesReturnParticulars' => ['required', 'array'],
            'salesReturnParticulars.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->withoutTrashed()],
            'salesReturnParticulars.*.unit_id' => ['required', Rule::exists('units', 'id')->withoutTrashed()],
            'salesReturnParticulars.*.warehouse_id' => ['required', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'salesReturnParticulars.*.quantity' => ['required', 'numeric'],
            'salesReturnParticulars.*.rate' => ['required', 'numeric'],
            'salesReturnParticulars.*.sales_tax_id' => ['nullable', Rule::exists('taxes', 'id')->withoutTrashed()],
            'salesReturnParticulars.*.sales_tax_amount' => ['nullable', 'numeric'],
            'salesReturnParticulars.*.discount_amount' => ['nullable', 'numeric'],
            'remarks' => ['nullable'],
        ];
    }
}
