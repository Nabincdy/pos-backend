<?php

namespace App\Http\Requests\Api\Inventory\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('brand_create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('brands', 'name')->withoutTrashed()],
            'code' => ['required', Rule::unique('brands', 'code')->withoutTrashed()],
            'logo' => ['nullable', 'image'],
        ];
    }
}
