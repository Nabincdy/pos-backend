<?php

namespace App\Http\Requests\Api\Account\Ledger;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateLedgerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('ledger_edit');
    }

    public function rules(): array
    {
        return [
            'ledger_group_id' => ['required', Rule::exists('ledger_groups', 'id')],
            'ledger_id' => ['nullable', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'ledger_name' => ['required', Rule::unique('ledgers', 'ledger_name')->withoutTrashed()->ignore($this->ledger)],
            'code' => ['required', Rule::unique('ledgers', 'code')->withoutTrashed()->ignore($this->ledger)],
            'category' => ['nullable'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'pan_no' => ['nullable', Rule::unique('ledgers', 'pan_no')->withoutTrashed()->ignore($this->ledger)],
        ];
    }
}
