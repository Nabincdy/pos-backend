<?php

namespace App\Http\Requests\Api\Crm\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('company_edit');
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', Rule::unique('companies', 'company_name')->withoutTrashed()->ignore($this->company)],
            'logo' => ['nullable', 'image'],
            'code' => ['required', Rule::unique('companies', 'code')->withoutTrashed()->ignore($this->company)],
            'phone' => ['nullable', Rule::unique('companies', 'phone')->withoutTrashed()->ignore($this->company)],
            'email' => ['nullable', 'email', Rule::unique('companies', 'email')->withoutTrashed()->ignore($this->company)],
            'landline' => ['nullable', Rule::unique('companies', 'landline')->withoutTrashed()->ignore($this->company)],
            'vat_pan_no' => ['nullable', Rule::unique('companies', 'vat_pan_no')->withoutTrashed()->ignore($this->company)],
            'address' => ['nullable'],
        ];
    }
}
