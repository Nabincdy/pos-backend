<?php

namespace App\Http\Controllers\Api\Sms;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sms\SentMessageResource;
use App\Models\Sms\SentMessage;
use App\Traits\SmsTrait;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    use SmsTrait;

    public function creditBalance()
    {
        return $this->getCreditBalance();
    }

    public function sendSingleSms(Request $request)
    {
        $this->checkAuthorization('sentMessage_access');

        $request->validate([
            'phone' => ['required'],
            'message' => ['required'],
        ]);
        $this->sendTextSMS($request->input('phone'), $request->input('message'));

        return response()->json([
            'data' => '',
            'message' => 'Sms Sent Successfully',
        ]);
    }

    public function sendGroupSms(Request $request)
    {
        $this->checkAuthorization('sentMessage_access');

        $request->validate([
            'message' => ['required'],
            'contacts' => ['required', 'array'],
            'contacts.*.name' => ['required_if:contacts.*.status,true', 'string', 'max:255'],
            'contacts.*.phone' => ['required_if:contacts.*.status,true'],
            'contacts.*.status' => ['required', 'boolean'],
        ]);

        foreach ($request->input('contacts') as $contact) {
            if ($contact['status']) {
                $this->sendTextSMS($contact['phone'], $request->input('message'), $contact['name']);
            }
        }

        return response()->json([
            'data' => '',
            'message' => 'Sms Sent Successfully',
        ]);
    }

    public function sentMessages(Request $request)
    {
        $this->checkAuthorization('sentMessage_report');

        $sentMessages = SentMessage::filterData($request->all())->latest()->get();

        return SentMessageResource::collection($sentMessages);
    }
}
