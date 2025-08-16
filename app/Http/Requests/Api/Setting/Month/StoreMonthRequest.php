<?php

namespace App\Http\Requests\Api\Setting\Month;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreMonthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('month_create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('months', 'name')->withoutTrashed()],
            'month' => ['required', 'string', 'max:2', Rule::unique('months', 'month')->withoutTrashed()],
            'rank' => ['nullable', 'integer'],
        ];
    }
}
