<?php

namespace App\Http\Requests\Api\Inventory\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('warehouse_create');
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'name' => ['required', Rule::unique('warehouses', 'name')->withoutTrashed()],
            'code' => ['required', Rule::unique('warehouses', 'code')->withoutTrashed()],
            'phone' => ['nullable'],
            'address' => ['nullable'],
        ];
    }
}
