<?php

namespace App\Http\Requests\Api\Crm\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('supplier_create');
    }

    public function rules(): array
    {
        return [
            'supplier_name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                Rule::unique('suppliers', 'code')->withoutTrashed(),
                Rule::unique('ledgers', 'code')->withoutTrashed(),
            ],
            'phone' => ['nullable'],
            'email' => ['nullable', 'email'],
            'profile_photo' => ['nullable', 'image'],
            'company_id' => ['nullable', Rule::exists('companies', 'id')->withoutTrashed()],
            'pan_no' => ['nullable', Rule::unique('suppliers', 'pan_no')->withoutTrashed()],
            'address' => ['nullable'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
