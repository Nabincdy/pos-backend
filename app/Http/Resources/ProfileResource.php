<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id ?? '',
            'name' => $this->name ?? '',
            'email' => $this->email ?? '',
            'role' => $this->role->title ?? '',
            'photo_url' => $this->photo_url ?? '',
            'phone' => $this->phone ?? '',
        ];
    }
}
