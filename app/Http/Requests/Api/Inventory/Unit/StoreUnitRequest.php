<?php

namespace App\Http\Requests\Api\Inventory\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('unit_create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('units', 'name')->withoutTrashed()],
            'code' => ['required', Rule::unique('units', 'code')->withoutTrashed()],
        ];
    }
}
