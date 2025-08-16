<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanySettingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'company_name' => $this->company_name ?? '',
            'phone' => $this->phone ?? '',
            'email' => $this->email ?? '',
            'address' => $this->address ?? '',
            'logo_url' => $this->logo_url ?? '',
            'pan_no' => $this->pan_no ?? '',
            'website_url' => $this->website_url ?? '',
            'facebook_url' => $this->facebook_url ?? '',
            'youtube_url' => $this->youtube_url ?? '',
            'code_prefixes' => [
                'bank_account' => $this->bank_account ?? '',
                'journal_voucher' => $this->journal_voucher ?? '',
                'payment_voucher' => $this->payment_voucher ?? '',
                'receipt_voucher' => $this->receipt_voucher ?? '',
                'client_group' => $this->client_group ?? '',
                'client' => $this->client ?? '',
                'company' => $this->company ?? '',
                'supplier' => $this->supplier ?? '',
                'product' => $this->product ?? '',
                'product_category' => $this->product_category ?? '',
                'warehouse' => $this->warehouse ?? '',
                'purchase' => $this->purchase ?? '',
                'purchase_return' => $this->purchase_return ?? '',
                'sales' => $this->sales ?? '',
                'sales_return' => $this->sales_return ?? '',
                'employee' => $this->employee ?? '',
                'payable_charge' => $this->payable_charge ?? '',
                'payslip' => $this->payslip ?? '',
                'tax' => $this->tax ?? '',
                'supplier_payment' => $this->supplier_payment ?? '',
                'client_payment' => $this->client_payment ?? '',
                'quotation' => $this->quotation ?? '',
            ],
        ];
    }
}
