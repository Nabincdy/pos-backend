<?php

namespace App\Http\Requests\Api\Hr\AdvanceSalary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateAdvanceSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('advanceSalary_edit');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', Rule::exists('employees', 'id')->withoutTrashed()],
            'date' => ['required'],
            'amount' => ['required', 'numeric'],
            'deduct_month_id' => ['required', Rule::exists('months', 'id')->withoutTrashed()],
            'remarks' => ['required'],
        ];
    }
}
