<?php

namespace App\Http\Requests\Api\Inventory\ProductOpening;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateProductOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('productOpening_edit');
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', Rule::exists('products', 'id')->withoutTrashed()],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'unit_id' => ['required', Rule::exists('units', 'id')->withoutTrashed()],
            'rate' => ['required', 'numeric'],
            'quantity' => ['required'],
            'opening_date' => ['required'],
            'en_opening_date' => ['required', 'date'],
            'batch_no' => ['nullable'],
            'expiry_date' => ['nullable'],
            'en_expiry_date' => ['nullable', 'date'],
            'remarks' => ['nullable'],
        ];
    }
}
