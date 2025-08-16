<?php

namespace App\Http\Requests\Api\Setting\FiscalYear;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreFiscalYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('fiscalYear_create');
    }

    public function rules(): array
    {
        return [
            'year' => ['required', Rule::unique('fiscal_years', 'year')->withoutTrashed()],
            'year_title' => ['required', 'string', 'max:255', Rule::unique('fiscal_years', 'year_title')->withoutTrashed()],
            'start_date' => ['required', 'date', Rule::unique('fiscal_years', 'start_date')->withoutTrashed()],
            'end_date' => ['required', 'date', 'after:start_date', Rule::unique('fiscal_years', 'end_date')->withoutTrashed()],
            'is_running' => ['nullable', 'boolean'],
        ];
    }
}
