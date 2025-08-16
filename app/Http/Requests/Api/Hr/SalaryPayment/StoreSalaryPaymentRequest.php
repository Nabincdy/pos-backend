<?php

namespace App\Http\Requests\Api\Hr\SalaryPayment;

use App\Rules\CheckDateMonthRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSalaryPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('salaryPayment_create');
    }

    public function rules(): array
    {
        return [
            'month_id' => ['required', Rule::exists('months', 'id')->withoutTrashed()],
            'payment_date' => ['required', new CheckDateMonthRule($this->month_id)],
            'employee_id' => ['required', Rule::exists('employees', 'id')->withoutTrashed()],
            'payment_method' => ['nullable', 'in:Cash,Bank'],
            'remarks' => ['nullable'],
            'paymentParticulars' => ['required', 'array'],
            'paymentParticulars.*.payable_charge_id' => ['required', Rule::exists('payable_charges', 'id')->withoutTrashed()],
            'paymentParticulars.*.amount' => ['required', 'numeric'],
        ];
    }
}
