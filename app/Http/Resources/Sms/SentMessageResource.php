<?php

namespace App\Http\Resources\Sms;

use Illuminate\Http\Resources\Json\JsonResource;

class SentMessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'date' => $this->created_at->toDateString(),
            'phone' => $this->phone ?? '',
            'message' => $this->message ?? '',
        ];
    }
}
