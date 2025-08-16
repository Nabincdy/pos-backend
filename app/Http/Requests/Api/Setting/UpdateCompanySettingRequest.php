<?php

namespace App\Http\Requests\Api\Setting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateCompanySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('companySetting_edit');
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required'],
            'phone' => ['nullable'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable'],
            'logo' => ['nullable', 'image'],
            'pan_no' => ['nullable'],
            'website_url' => ['nullable', 'url'],
            'facebook_url' => ['nullable', 'url'],
            'youtube_url' => ['nullable', 'url'],
            'code_prefixes.bank_account' => ['required'],
            'code_prefixes.journal_voucher' => ['required'],
            'code_prefixes.payment_voucher' => ['required'],
            'code_prefixes.receipt_voucher' => ['required'],
            'code_prefixes.client_group' => ['required'],
            'code_prefixes.client' => ['required'],
            'code_prefixes.company' => ['required'],
            'code_prefixes.supplier' => ['required'],
            'code_prefixes.product' => ['required'],
            'code_prefixes.product_category' => ['required'],
            'code_prefixes.warehouse' => ['required'],
            'code_prefixes.purchase' => ['required'],
            'code_prefixes.purchase_return' => ['required'],
            'code_prefixes.sales' => ['required'],
            'code_prefixes.sales_return' => ['required'],
            'code_prefixes.employee' => ['required'],
            'code_prefixes.payable_charge' => ['required'],
            'code_prefixes.payslip' => ['required'],
            'code_prefixes.tax' => ['required'],
            'code_prefixes.supplier_payment' => ['required'],
            'code_prefixes.client_payment' => ['required'],
            'code_prefixes.quotation' => ['required'],
        ];
    }
}
