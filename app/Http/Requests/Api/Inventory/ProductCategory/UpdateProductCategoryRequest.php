<?php

namespace App\Http\Requests\Api\Inventory\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('productCategory_edit');
    }

    public function rules(): array
    {
        return [
            'product_category_id' => ['nullable', Rule::exists('product_categories', 'id')->withoutTrashed()],
            'name' => ['required', Rule::unique('product_categories', 'name')->withoutTrashed()->ignore($this->productCategory)],
            'code' => ['required', Rule::unique('product_categories', 'code')->withoutTrashed()->ignore($this->productCategory)],
            'image' => ['nullable', 'image'],
            'description' => ['nullable'],
        ];
    }
}
