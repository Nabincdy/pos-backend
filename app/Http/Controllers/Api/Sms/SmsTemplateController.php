<?php

namespace App\Http\Controllers\Api\Sms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Sms\StoreSmsTemplateRequest;
use App\Http\Requests\Api\Sms\UpdateSmsTemplateRequest;
use App\Http\Resources\Sms\SmsTemplateResource;
use App\Models\Sms\SmsTemplate;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $this->checkAuthorization('smsTemplate_access');

        return SmsTemplateResource::collection(SmsTemplate::all());
    }

    public function store(StoreSmsTemplateRequest $request)
    {
        $this->checkAuthorization('smsTemplate_create');

        $smsTemplate = SmsTemplate::create($request->validated());

        return response()->json([
            'data' => SmsTemplateResource::make($smsTemplate),
            'message' => 'Sms Template Added Successfully',
        ], 201);
    }

    public function show(SmsTemplate $smsTemplate)
    {
        $this->checkAuthorization('smsTemplate_access');

        return SmsTemplateResource::make($smsTemplate);
    }

    public function update(UpdateSmsTemplateRequest $request, SmsTemplate $smsTemplate)
    {
        $this->checkAuthorization('smsTemplate_edit');

        $smsTemplate->update($request->validated());

        return response()->json([
            'data' => SmsTemplateResource::make($smsTemplate),
            'message' => 'Sms Template Updated Successfully',
        ]);
    }

    public function destroy(SmsTemplate $smsTemplate)
    {
        $this->checkAuthorization('smsTemplate_delete');

        $smsTemplate->delete();

        return response()->json([
            'data' => '',
            'message' => 'Sms Template Deleted Successfully',
        ]);
    }
}
