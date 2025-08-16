<?php

namespace App\Http\Requests\Api\Hr\PayableCharge;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayableChargeRequest extends FormRequest
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
