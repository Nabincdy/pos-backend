<?php

namespace App\Http\Requests\Api\Setting\Bank;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('bank_create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('banks', 'name')->withoutTrashed()],
            'code' => ['required', 'string', 'max:255', Rule::unique('banks', 'code')->withoutTrashed()],
        ];
    }
}
