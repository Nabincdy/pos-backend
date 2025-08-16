<?php

namespace App\Http\Requests\Api\Hr\LeaveType;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('leaveType_create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', Rule::unique('leave_types', 'title')->withoutTrashed()],
        ];
    }
}
