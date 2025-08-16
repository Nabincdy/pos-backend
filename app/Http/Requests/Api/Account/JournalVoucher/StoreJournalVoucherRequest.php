<?php

namespace App\Http\Requests\Api\Account\JournalVoucher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreJournalVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('journalVoucher_create');
    }

    public function rules(): array
    {
        return [
            'voucher_no' => ['required', Rule::unique('journal_vouchers', 'voucher_no')->withoutTrashed()],
            'voucher_date' => ['required'],
            'remarks' => ['nullable'],
            'journalVoucherParticulars' => ['required', 'array'],
            'journalVoucherParticulars.*.ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'journalVoucherParticulars.*.dr_amount' => ['nullable', 'numeric'],
            'journalVoucherParticulars.*.cr_amount' => ['nullable', 'numeric'],
            'journalVoucherParticulars.*.remarks' => ['nullable'],
        ];
    }
}
