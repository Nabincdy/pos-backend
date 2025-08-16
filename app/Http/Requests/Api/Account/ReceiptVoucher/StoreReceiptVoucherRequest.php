<?php

namespace App\Http\Requests\Api\Account\ReceiptVoucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreReceiptVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('receiptVoucher_create');
    }

    public function rules(): array
    {
        return [
            'receipt_no' => ['required', Rule::unique('receipt_vouchers', 'receipt_no')->withoutTrashed()],
            'receipt_date' => ['required'],
            'receipt_method' => ['required', Rule::in(['Cash', 'Bank'])],
            'cash_bank_ledger_id' => ['required_if:receipt_method,Bank'],
            'remarks' => ['nullable'],
            'voucherParticulars' => ['required', 'array'],
            'voucherParticulars.*.ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'voucherParticulars.*.amount' => ['nullable', 'numeric'],
        ];
    }
}
