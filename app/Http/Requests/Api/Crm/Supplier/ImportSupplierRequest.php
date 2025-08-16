<?php

namespace App\Http\Requests\Api\Crm\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class ImportSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'excel_file' => ['required', 'file', 'mimes:xlsx,txt'],
        ];
    }
}
