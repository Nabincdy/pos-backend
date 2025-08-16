<?php

namespace App\Http\Requests\Api\Inventory\ReceiptRecord;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreReceiptRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('receiptRecord_create');
    }

    public function rules(): array
    {
        return [
            'receipt_date' => ['required'],
            'payment_method' => ['required', 'in:Cash,Bank'],
            'bank_account_ledger_id' => ['required_if:payment_method,Bank'],
            'client_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'sales' => ['required', 'array'],
            'sales.*.sale_id' => ['required', Rule::exists('sales', 'id')->withoutTrashed()],
            'sales.*.amount' => ['required', 'numeric'],
            'remarks' => ['nullable'],
            'send_sms' => ['nullable', 'boolean'],
        ];
    }
}
