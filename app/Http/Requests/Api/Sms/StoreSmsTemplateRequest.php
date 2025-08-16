<?php

namespace App\Http\Requests\Api\Sms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSmsTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('smsTemplate_create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', Rule::unique('sms_templates', 'title')->withoutTrashed()],
            'message' => ['required'],
        ];
    }
}
