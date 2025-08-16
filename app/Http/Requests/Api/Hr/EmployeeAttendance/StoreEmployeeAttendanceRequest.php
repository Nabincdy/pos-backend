<?php

namespace App\Http\Requests\Api\Hr\EmployeeAttendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreEmployeeAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('employeeAttendance_create');
    }

    public function rules(): array
    {
        return [
            'date' => ['required'],
            'employees' => ['required', 'array'],
            'employees.*.employee_id' => ['required', Rule::exists('employees', 'id')->withoutTrashed()],
            'employees.*.attendance_id' => ['nullable', Rule::exists('employee_attendances', 'id')->withoutTrashed()],
            'employees.*.status' => ['required', 'in:Present,Absent'],
            'employees.*.in_time' => ['required_if:employees.*.status,Present'],
            'employees.*.out_time' => ['required_if:employees.*.status,Present'],
            'employees.*.remarks' => ['nullable'],
        ];
    }
}
