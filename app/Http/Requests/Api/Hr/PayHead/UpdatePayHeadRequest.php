<?php

namespace App\Http\Requests\Api\Hr\PayHead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdatePayHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('payHead_edit');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('pay_heads', 'name')->withoutTrashed()->ignore($this->payHead)],
            'ledger_id' => ['required', Rule::exists('ledgers', 'id')->withoutTrashed()],
            'type' => ['required', 'in:Addition,Deduction'],
            'is_taxable' => ['nullable', 'boolean'],
            'tax_id' => ['required_if:is_taxable,true'],
        ];
    }
}
