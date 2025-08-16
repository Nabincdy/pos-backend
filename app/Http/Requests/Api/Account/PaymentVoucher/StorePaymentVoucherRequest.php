<?php

namespace App\Http\Requests\Api\Account\PaymentVoucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePaymentVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('paymentVoucher_create');
    }

    public function rules(): array
    {
        return [
            'voucher_no' => ['required', Rule::unique('payment_vouchers', 'voucher_no')->withoutTrashed()],
            'payment_date' => ['required'],
            'payment_method' => ['required', 'in:Cash,Bank'],
            'bank_account_ledger_id' => ['required_if:payment_method,Bank'],
            'remarks' => ['nullable'],
            'voucherParticulars' => ['required', 'array'],
            'voucherParticulars.*.ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'voucherParticulars.*.amount' => ['nullable', 'numeric'],
        ];
    }
}
