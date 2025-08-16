<?php

namespace App\Http\Requests\Api\Inventory\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('unit_edit');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('units', 'name')->withoutTrashed()->ignore($this->unit)],
            'code' => ['required', Rule::unique('units', 'code')->withoutTrashed()->ignore($this->unit)],
        ];
    }
}
