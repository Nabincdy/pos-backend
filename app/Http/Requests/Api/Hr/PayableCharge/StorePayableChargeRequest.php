<?php

namespace App\Http\Requests\Api\Hr\PayableCharge;

use App\Rules\CheckDateMonthRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePayableChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('payableCharge_create');
    }

    public function rules(): array
    {
        return [
            'month_id' => ['required', Rule::exists('months', 'id')->withoutTrashed()],
            'date' => ['required', new CheckDateMonthRule($this->month_id)],
            'employees' => ['required', 'array'],
            'employees.*' => [Rule::exists('employees', 'id')->withoutTrashed()],
        ];
    }
}
