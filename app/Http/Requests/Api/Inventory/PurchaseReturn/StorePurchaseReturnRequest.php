<?php

namespace App\Http\Requests\Api\Inventory\PurchaseReturn;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('purchaseReturn_create');
    }

    public function rules(): array
    {
        return [
            'invoice_no' => ['required', Rule::unique('purchase_returns', 'invoice_no')->withoutTrashed()],
            'return_date' => ['required'],
            'supplier_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'purchaseReturnParticulars' => ['required', 'array'],
            'purchaseReturnParticulars.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->withoutTrashed()],
            'purchaseReturnParticulars.*.unit_id' => ['required', Rule::exists('units', 'id')->withoutTrashed()],
            'purchaseReturnParticulars.*.warehouse_id' => ['required', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'purchaseReturnParticulars.*.quantity' => ['required', 'numeric'],
            'purchaseReturnParticulars.*.rate' => ['required', 'numeric'],
            'purchaseReturnParticulars.*.purchase_tax_id' => ['nullable', Rule::exists('taxes', 'id')->withoutTrashed()],
            'purchaseReturnParticulars.*.purchase_tax_amount' => ['nullable', 'numeric'],
            'purchaseReturnParticulars.*.discount_amount' => ['nullable', 'numeric'],
            'remarks' => ['nullable'],
        ];
    }
}
