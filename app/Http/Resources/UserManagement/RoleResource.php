<?php

namespace App\Http\Resources\UserManagement;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'title' => $this->title ?? '',
            'permissions' => PermissionResource::collection($this->permissions),
        ];
    }
}
