<?php

namespace App\Http\Requests\Api\Inventory\Sale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('sale_create');
    }

    public function rules(): array
    {
        return [
            'invoice_no' => ['required', Rule::unique('sales', 'invoice_no')->withoutTrashed()],
            'sales_date' => ['required'],
            'en_sales_date' => ['required', 'date'],
            'payment_type' => ['required', 'in:Credit,Cash,Bank'],
            'bank_account_ledger_id' => ['required_if:payment_type,Bank'],
            'paid_amount' => ['required_if:payment_type,Cash,Bank', 'numeric'],
            'client_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'saleParticulars' => ['required', 'array'],
            'saleParticulars.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->withoutTrashed()],
            'saleParticulars.*.unit_id' => ['required', Rule::exists('units', 'id')->withoutTrashed()],
            'saleParticulars.*.warehouse_id' => [Rule::requiredIf(empty($this->warehouse_id))],
            'saleParticulars.*.quantity' => ['required', 'numeric'],
            'saleParticulars.*.rate' => ['required', 'numeric'],
            'saleParticulars.*.batch_no' => ['nullable'],
            'saleParticulars.*.expiry_date' => ['nullable'],
            'saleParticulars.*.en_expiry_date' => ['nullable', 'date'],
            'saleParticulars.*.sales_tax_id' => ['nullable', Rule::exists('taxes', 'id')->withoutTrashed()],
            'saleParticulars.*.sales_tax_rate' => ['nullable', 'numeric'],
            'saleParticulars.*.sales_tax_amount' => ['nullable', 'numeric'],
            'saleParticulars.*.discount_amount' => ['nullable', 'numeric'],
            'payment_remarks' => ['nullable'],
            'remarks' => ['nullable'],
        ];
    }
}
