<?php

namespace App\Http\Requests\Api\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateAccountSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('accountSetting_edit');
    }

    public function rules(): array
    {
        return [
            'cash_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'bank_ledger_group_id' => ['required', Rule::exists('ledger_groups', 'id')->withoutTrashed()],
            'supplier_ledger_group_id' => ['required', Rule::exists('ledger_groups', 'id')->withoutTrashed()],
            'client_ledger_group_id' => ['required', Rule::exists('ledger_groups', 'id')->withoutTrashed()],
            'tax_ledger_group_id' => ['required', Rule::exists('ledger_groups', 'id')->withoutTrashed()],
            'purchase_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'sales_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'advance_salary_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'salary_payable_ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
        ];
    }
}
