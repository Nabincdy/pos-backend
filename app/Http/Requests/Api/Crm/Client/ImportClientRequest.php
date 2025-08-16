<?php

namespace App\Http\Requests\Api\Crm\Client;

use Illuminate\Foundation\Http\FormRequest;

class ImportClientRequest extends FormRequest
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
