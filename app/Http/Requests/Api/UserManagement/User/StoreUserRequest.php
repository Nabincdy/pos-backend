<?php

namespace App\Http\Requests\Api\UserManagement\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('user_create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', Rule::unique('users', 'name')->withoutTrashed()],
            'email' => ['required', 'email', Rule::unique('users', 'email')->withoutTrashed()],
            'password' => ['required', 'min:7', 'confirmed'],
            'photo' => ['nullable', 'mimes:png,jpg,jpeg'],
            'phone' => ['nullable', Rule::unique('users', 'phone')->withoutTrashed()],
            'status_at' => ['nullable'],
            'role_id' => ['required', Rule::exists('roles', 'id')->withoutTrashed()],
        ];
    }
}
