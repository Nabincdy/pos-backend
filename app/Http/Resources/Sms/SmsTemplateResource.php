<?php

namespace App\Http\Resources\Sms;

use Illuminate\Http\Resources\Json\JsonResource;

class SmsTemplateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? '',
            'title' => $this->title ?? '',
            'message' => $this->message ?? '',
        ];
    }
}
