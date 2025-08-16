<?php

namespace App\Http\Requests\Api\Account\LedgerGroup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateLedgerGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('ledgerGroup_edit');
    }

    public function rules(): array
    {
        return [
            'ledger_group_id' => ['required', Rule::exists('ledger_groups', 'id')->withoutTrashed()],
            'group_name' => ['required', Rule::unique('ledger_groups', 'group_name')->withoutTrashed()->ignore($this->ledgerGroup)],
            'code' => ['required', Rule::unique('ledger_groups', 'code')->withoutTrashed()->ignore($this->ledgerGroup)],
        ];
    }
}
