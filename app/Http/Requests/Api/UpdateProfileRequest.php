<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('users', 'name')->ignore(auth()->user())],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->user())],
            'phone' => ['nullable'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
        ];
    }
}
