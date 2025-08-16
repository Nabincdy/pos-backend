<?php

namespace App\Http\Requests\Api\Hr\EmployeeAttendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateEmployeeAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('employeeAttendance_edit');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', Rule::exists('employees', 'id')->withoutTrashed()],
            'date' => ['required', 'date'],
            'status' => ['nullable'],
            'in_time' => ['nullable'],
            'out_time' => ['nullable'],
            'remarks' => ['nullable'],
        ];
    }
}
