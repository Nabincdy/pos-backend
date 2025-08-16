<?php

namespace App\Http\Requests\Api\UserManagement\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('role_edit');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', Rule::unique('roles', 'title')->withoutTrashed()->ignore($this->role)],
            'permissions' => ['required', 'array'],
            'permissions.*' => [Rule::exists('permissions', 'id')->withoutTrashed()],
        ];
    }
}
