<?php

namespace App\Http\Requests\Api\Hr\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('employee_create');
    }

    public function rules(): array
    {
        return [

            'role_id' => ['nullable', Rule::exists('roles', 'id')->withoutTrashed()],
            'name' => ['required', 'string', 'max:255', Rule::unique('employees', 'name')->withoutTrashed()],
            'code' => ['required'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'dob' => ['nullable', 'date'],
            'rank' => ['nullable', 'integer'],
            'email' => ['nullable', 'required_with:role_id', 'email', Rule::unique('employees', 'email')->withoutTrashed(), Rule::unique('users', 'email')->withoutTrashed()],
            'phone' => ['nullable', Rule::unique('employees', 'phone')->withoutTrashed()],
            'photo' => ['nullable', 'image'],
            'department_id' => ['required', Rule::exists('departments', 'id')->withoutTrashed()],
            'designation_id' => ['required', Rule::exists('designations', 'id')->withoutTrashed()],
            'joining_date' => ['nullable', 'date'],
            'marital_status' => ['nullable'],
            'citizenship_no' => ['nullable'],
            'pan_no' => ['nullable', 'integer'],
            'signature' => ['nullable', 'image'],
            'address' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
