<?php

namespace App\Http\Requests\Api\Hr\Designation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateDesignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('designation_edit');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('designations', 'name')->withoutTrashed()->ignore($this->designation)],
        ];
    }
}
