<?php

namespace App\Http\Requests\Api\Account\BankAccount;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateBankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('bankAccount_edit');
    }

    public function rules(): array
    {
        return [
            'bank_id' => ['required', Rule::exists('banks', 'id')->withoutTrashed()],
            'branch' => ['nullable'],
            'account_name' => ['required'],
            'account_no' => ['required', Rule::unique('bank_accounts', 'account_no')->where('bank_id', $this->bank_id)->withoutTrashed()->ignore($this->bankAccount)],
            'code' => ['required', Rule::unique('bank_accounts', 'code')->withoutTrashed()->ignore($this->bankAccount)],
        ];
    }
}
