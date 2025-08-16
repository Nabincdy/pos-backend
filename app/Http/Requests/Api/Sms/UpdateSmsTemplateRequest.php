<?php

namespace App\Http\Requests\Api\Sms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateSmsTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('smsTemplate_edit');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', Rule::unique('sms_templates', 'title')->withoutTrashed()->ignore($this->smsTemplate)],
            'message' => ['required'],
        ];
    }
}
