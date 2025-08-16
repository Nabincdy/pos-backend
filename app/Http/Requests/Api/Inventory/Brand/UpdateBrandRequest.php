<?php

namespace App\Http\Requests\Api\Inventory\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('brand_edit');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('brands', 'name')->withoutTrashed()->ignore($this->brand)],
            'code' => ['required', Rule::unique('brands', 'code')->withoutTrashed()->ignore($this->brand)],
            'logo' => ['nullable', 'image'],
        ];
    }
}
