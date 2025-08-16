<?php

namespace App\Http\Resources\Crm;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'group_name' => $this->group_name ?? '',
            'code' => $this->code ?? '',
        ];
    }
}
