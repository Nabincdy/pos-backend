<?php

namespace App\Http\Requests\Api\Hr\EmployeeSalary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeSalaryRequest extends FormRequest
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
