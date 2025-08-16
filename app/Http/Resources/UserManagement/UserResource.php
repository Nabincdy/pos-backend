<?php

namespace App\Http\Resources\UserManagement;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'name' => $this->name ?? '',
            'email' => $this->email ?? '',
            'photo_url' => $this->photo_url ?? '',
            'phone' => $this->phone ?? '',
            'role_id' => $this->role_id ?? '',
            'role' => $this->whenLoaded('role', function () {
                return $this->role->title ?? '';
            }),
            'status' => ! $this->status_at ?? null,
        ];
    }
}
