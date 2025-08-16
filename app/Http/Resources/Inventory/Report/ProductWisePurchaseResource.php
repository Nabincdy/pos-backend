<?php

namespace App\Http\Resources\Inventory\Report;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductWisePurchaseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product_name' => $this->name ?? '',
            'purchaseParticulars' => ProductWisePurchaseParticularResource::collection($this->purchaseParticulars),
        ];
    }
}
