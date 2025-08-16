<?php

namespace App\Http\Requests\Api\Crm\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('company_create');
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', Rule::unique('companies', 'company_name')->withoutTrashed()],
            'logo' => ['nullable', 'image'],
            'code' => ['required', Rule::unique('companies', 'code')->withoutTrashed()],
            'phone' => ['nullable', Rule::unique('companies', 'phone')->withoutTrashed()],
            'email' => ['nullable', 'email', Rule::unique('companies', 'email')->withoutTrashed()],
            'landline' => ['nullable', Rule::unique('companies', 'landline')->withoutTrashed()],
            'vat_pan_no' => ['nullable', Rule::unique('companies', 'vat_pan_no')->withoutTrashed()],
            'address' => ['nullable'],
        ];
    }
}
