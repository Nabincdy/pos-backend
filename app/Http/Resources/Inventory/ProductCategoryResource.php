<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'product_category_id' => $this->product_category_id ?? '',
            'name' => $this->name ?? '',
            'code' => $this->code ?? '',
            'image_url' => $this->image_url ?? '',
            'description' => $this->description ?? '',
            'sub_categories' => ProductCategoryResource::collection($this->whenLoaded('productCategories')),
        ];
    }
}
