<?php

namespace App\Http\Requests\Api\Inventory\Quotation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('quotation_create');
    }

    public function rules(): array
    {
        return [
            'invoice_no' => ['required', Rule::unique('sales', 'invoice_no')->withoutTrashed()],
            'quotation_date' => ['required'],
            'client_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'quotationParticulars' => ['required', 'array'],
            'quotationParticulars.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->withoutTrashed()],
            'quotationParticulars.*.unit_id' => ['required', Rule::exists('units', 'id')->withoutTrashed()],
            'quotationParticulars.*.warehouse_id' => ['required', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'quotationParticulars.*.quantity' => ['required', 'numeric'],
            'quotationParticulars.*.rate' => ['required', 'numeric'],
            'quotationParticulars.*.sales_tax_id' => ['nullable', Rule::exists('taxes', 'id')->withoutTrashed()],
            'quotationParticulars.*.sales_tax_rate' => ['nullable', 'numeric'],
            'quotationParticulars.*.sales_tax_amount' => ['nullable', 'numeric'],
            'quotationParticulars.*.discount_amount' => ['nullable', 'numeric'],
            'remarks' => ['nullable'],
        ];
    }
}
