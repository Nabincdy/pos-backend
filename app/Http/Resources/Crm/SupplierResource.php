<?php

namespace App\Http\Resources\Crm;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'supplier_name' => $this->supplier_name ?? '',
            'code' => $this->code ?? '',
            'phone' => $this->phone ?? '',
            'ledger_id' => $this->ledger_id ?? '',
            'email' => $this->email ?? '',
            'profile_photo_url' => $this->profile_photo_url ?? '',
            'company_id' => $this->company_id ?? '',
            'company_name' => $this->whenLoaded('company', function () {
                return $this->company->company_name ?? '';
            }),
            'pan_no' => $this->pan_no ?? '',
            'address' => $this->address ?? '',
            'status' => $this->status ?? false,
        ];
    }
}
