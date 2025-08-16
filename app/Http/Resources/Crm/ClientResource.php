<?php

namespace App\Http\Resources\Crm;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'client_group_id' => $this->client_group_id ?? '',
            'client_group' => $this->whenLoaded('clientGroup', function () {
                return $this->clientGroup->group_name ?? '';
            }),
            'name' => $this->name ?? '',
            'code' => $this->code ?? '',
            'phone' => $this->phone ?? '',
            'email' => $this->email ?? '',
            'ledger_id' => $this->ledger_id ?? '',
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
