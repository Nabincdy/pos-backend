<?php

namespace App\Http\Resources\Inventory\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductWiseSalesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product_name' => $this->name ?? '',
            'saleParticulars' => ProductWiseSalesParticularResource::collection($this->saleParticulars),
        ];
    }
}
