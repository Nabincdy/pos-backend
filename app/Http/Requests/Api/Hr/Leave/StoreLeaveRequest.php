<?php

namespace App\Http\Requests\Api\Hr\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('leave_create');
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', Rule::exists('employees', 'id')->withoutTrashed()],
            'leave_type_id' => ['required', Rule::exists('leave_types', 'id')->withoutTrashed()],
            'issued_date' => ['required', 'date'],
            'start_date' => ['required', 'date', Rule::unique('leaves', 'id')->withoutTrashed()],
            'end_date' => ['required', 'date', 'after:start_date', Rule::unique('leaves', 'id')->withoutTrashed()],
            'reason' => ['nullable'],
            'status' => ['nullable', Rule::in(['Pending', 'Cancelled', 'Active'])],
        ];
    }
}
