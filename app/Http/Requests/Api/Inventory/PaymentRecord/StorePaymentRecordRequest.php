<?php

namespace App\Http\Requests\Api\Inventory\PaymentRecord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePaymentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('paymentRecord_create');
    }

    public function rules(): array
    {
        return [
            'payment_date' => ['required'],
            'payment_method' => ['required', 'in:Cash,Bank'],
            'bank_account_ledger_id' => ['required_if:payment_method,Bank'],
            'supplier_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'purchases' => ['required', 'array'],
            'purchases.*.purchase_id' => ['required', Rule::exists('purchases', 'id')->withoutTrashed()],
            'purchases.*.paid_amount' => ['required', 'numeric'],
            'remarks' => ['nullable'],
        ];
    }
}
