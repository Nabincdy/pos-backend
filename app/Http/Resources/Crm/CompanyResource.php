<?php

namespace App\Http\Resources\Crm;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'company_name' => $this->company_name ?? '',
            'logo_url' => $this->logo_url ?? '',
            'code' => $this->code ?? '',
            'phone' => $this->phone ?? '',
            'email' => $this->email ?? '',
            'landline' => $this->landline ?? '',
            'vat_pan_no' => $this->vat_pan_no ?? '',
            'address' => $this->address ?? '',
        ];
    }
}
