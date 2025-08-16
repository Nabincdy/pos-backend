<?php

namespace App\Http\Requests\Api\Crm\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('client_create');
    }

    public function rules(): array
    {
        return [
            'client_group_id' => ['required', Rule::exists('client_groups', 'id')->withoutTrashed()],
            'name' => ['required'],
            'code' => ['required', Rule::unique('clients', 'code')->withoutTrashed()],
            'phone' => ['nullable'],
            'email' => ['nullable', 'email'],
            'profile_photo' => ['nullable', 'image'],
            'company_id' => ['nullable', Rule::exists('companies', 'id')->withoutTrashed()],
            'pan_no' => ['nullable', Rule::unique('clients', 'pan_no')->withoutTrashed()],
            'address' => ['nullable'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
