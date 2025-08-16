<?php

namespace App\Http\Requests\Api\Setting\Tax;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('tax_create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('taxes', 'name')->withoutTrashed()],
            'code' => ['required', 'string', 'max:255', Rule::unique('taxes', 'code')->withoutTrashed()],
            'rate' => ['required', 'numeric'],
        ];
    }
}
