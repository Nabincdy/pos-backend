<?php

namespace App\Http\Requests\Api\Setting\Tax;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('tax_edit');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('taxes', 'name')->withoutTrashed()->ignore($this->tax)],
            'code' => ['required', 'string', 'max:255', Rule::unique('taxes', 'code')->withoutTrashed()->ignore($this->tax)],
            'rate' => ['required', 'numeric'],
        ];
    }
}
