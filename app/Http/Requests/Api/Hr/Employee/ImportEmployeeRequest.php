<?php

namespace App\Http\Requests\Api\Hr\Employee;

use Illuminate\Foundation\Http\FormRequest;

class ImportEmployeeRequest extends FormRequest
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
