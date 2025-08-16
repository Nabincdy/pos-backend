<?php

namespace App\Http\Requests\Api\Inventory\Purchase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('purchase_create');
    }

    public function rules(): array
    {
        return [
            'invoice_no' => ['required', Rule::unique('purchases', 'invoice_no')->withoutTrashed()],
            'purchase_date' => ['required'],
            'en_purchase_date' => ['required', 'date'],
            'payment_type' => ['required', 'in:Cash,Credit,Bank'],
            'bank_account_ledger_id' => ['required_if:payment_type,Bank'],
            'paid_amount' => ['required_if:payment_type,Cash,Bank', 'numeric'],
            'supplier_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'purchaseParticulars' => ['required', 'array'],
            'purchaseParticulars.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->withoutTrashed()],
            'purchaseParticulars.*.unit_id' => ['required', Rule::exists('units', 'id')->withoutTrashed()],
            'purchaseParticulars.*.warehouse_id' => ['required', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'purchaseParticulars.*.quantity' => ['required', 'numeric'],
            'purchaseParticulars.*.product_rate' => ['required', 'numeric'],
            'purchaseParticulars.*.batch_no' => [
                'nullable',
                Rule::unique('purchase_particulars', 'batch_no')->whereIn('product_id', Arr::pluck(request()->input('purchaseParticulars'), 'product_id'))->withoutTrashed()
            ],
            'purchaseParticulars.*.expiry_date' => ['nullable'],
            'purchaseParticulars.*.en_expiry_date' => ['nullable', 'date'],
            'purchaseParticulars.*.purchase_tax_id' => ['nullable', Rule::exists('taxes', 'id')->withoutTrashed()],
            'purchaseParticulars.*.purchase_tax_amount' => ['nullable', 'numeric'],
            'purchaseParticulars.*.discount_amount' => ['nullable', 'numeric'],
            'remarks' => ['nullable'],
        ];
    }
}
