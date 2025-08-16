<?php

namespace App\Http\Requests\Api\UserManagement\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('user_edit');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', Rule::unique('users', 'name')->withoutTrashed()->ignore($this->user)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->withoutTrashed()->ignore($this->user)],
            'photo' => ['nullable', 'mimes:png,jpg,jpeg'],
            'phone' => ['nullable', Rule::unique('users', 'phone')->withoutTrashed()->ignore($this->user)],
            'status_at' => ['nullable'],
            'role_id' => ['required', Rule::exists('roles', 'id')->withoutTrashed()],
        ];
    }
}
