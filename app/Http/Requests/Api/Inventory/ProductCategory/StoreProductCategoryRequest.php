<?php

namespace App\Http\Requests\Api\Inventory\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('productCategory_create');
    }

    public function rules(): array
    {
        return [
            'product_category_id' => ['nullable', Rule::exists('product_categories', 'id')->withoutTrashed()],
            'name' => ['required', Rule::unique('product_categories', 'name')->withoutTrashed()],
            'code' => ['required', Rule::unique('product_categories', 'code')->withoutTrashed()],
            'image' => ['nullable', 'image'],
            'description' => ['nullable'],
        ];
    }
}
