<?php

namespace App\Http\Requests\Api\Crm\ClientGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateClientGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('clientGroup_edit');
    }

    public function rules(): array
    {
        return [
            'group_name' => ['required', Rule::unique('client_groups', 'group_name')->withoutTrashed()->ignore($this->clientGroup)],
            'code' => ['required', Rule::unique('client_groups', 'code')->withoutTrashed()->ignore($this->clientGroup)],
        ];
    }
}
