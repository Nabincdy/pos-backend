<?php

namespace App\Http\Requests\Api;

use App\Rules\CheckCurrentPasswordRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', new CheckCurrentPasswordRule()],
            'password' => ['required', 'confirmed'],
        ];
    }
}
