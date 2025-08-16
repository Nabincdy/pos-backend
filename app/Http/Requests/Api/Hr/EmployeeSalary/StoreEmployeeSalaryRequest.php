<?php

namespace App\Http\Requests\Api\Hr\EmployeeSalary;

use App\Models\Hr\EmployeeSalary;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('employeeSalary_create');
    }

    public function rules(): array
    {
        return [
            'effective_from' => ['required', 'after:'.$this->lastEffectiveDate() ?? null],
            'salaryStructures' => ['required', 'array'],
            'salaryStructures.*.pay_head_id' => ['required', 'distinct', Rule::exists('pay_heads', 'id')->withoutTrashed()],
            'salaryStructures.*.amount' => ['required', 'numeric'],
        ];
    }

    private function lastEffectiveDate()
    {
        $lastSalary = EmployeeSalary::where('employee_id', $this->employee->id)->orderByDesc('effective_from')->first();

        return $lastSalary->effective_from ?? null;
    }
}
