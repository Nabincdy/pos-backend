<?php

namespace App\Http\Requests\Api\Account\AccountOpeningBalance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreAccountOpeningBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('accountOpeningBalance_create');
    }

    public function rules(): array
    {
        return [
            'ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'opening_date' => ['nullable'],
            'remarks' => ['nullable'],
        ];
    }
}
