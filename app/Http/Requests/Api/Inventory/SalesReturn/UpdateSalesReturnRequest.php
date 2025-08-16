<?php

namespace App\Http\Requests\Api\Inventory\SalesReturn;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

        ];
    }
}
