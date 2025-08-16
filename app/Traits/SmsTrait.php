<?php

namespace App\Traits;

use App\Models\Sms;

trait SmsTrait
{
    public function sendTextSMS($contact, $message, $name = '')
    {
        $sms_api_key = config('app.sms_api_key');
        $sms_api_url = config('app.sms_api_url');

        $data = [
            'mobile' => $contact,
            'message' => $message,
        ];

        if ($contact) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization:Bearer $sms_api_key",
                'content-Type:application/json',
                'Accept:application/json',
            ]);
            curl_setopt($ch, CURLOPT_URL, "$sms_api_url/sms");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($ch);
            curl_close($ch);

            return self::storeSmsDetail($contact, $message, $name, $response);
        }
    }

    public function getCreditBalance(): bool|string
    {
        $sms_api_key = config('app.sms_api_key');
        $sms_api_url = config('app.sms_api_url');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization:Bearer $sms_api_key",
            'content-Type:application/json',
            'Accept:application/json',
        ]);
        curl_setopt($ch, CURLOPT_URL, "$sms_api_url/balance");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function storeSmsDetail($contact, mixed $message, $name, bool|string $response)
    {
        return Sms\SentMessage::create([
            'phone' => $contact,
            'message' => $message,
            'name' => $name,
            'response_data' => $response,
        ]);
    }
}
