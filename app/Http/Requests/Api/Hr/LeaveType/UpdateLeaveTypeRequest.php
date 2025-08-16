<?php

namespace App\Http\Requests\Api\Hr\LeaveType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('leaveType_edit');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', Rule::unique('leave_types', 'title')->withoutTrashed()->ignore($this->leaveType)],
        ];
    }
}
