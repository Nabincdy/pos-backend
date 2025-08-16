<?php

namespace App\Http\Requests\Api\Inventory\Warehouse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('warehouse_edit');
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['nullable', Rule::exists('warehouses', 'id')->withoutTrashed()],
            'name' => ['required', Rule::unique('warehouses', 'name')->withoutTrashed()->ignore($this->warehouse)],
            'code' => ['required', Rule::unique('warehouses', 'code')->withoutTrashed()->ignore($this->warehouse)],
            'phone' => ['nullable'],
            'address' => ['nullable'],
        ];
    }
}
