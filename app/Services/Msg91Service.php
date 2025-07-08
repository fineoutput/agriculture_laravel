<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Msg91Service
{
    public function sendTestSMS($mobile, $message)
    {
        $response = Http::withHeaders([
            'authkey' => config('constants.MSG91_AUTH_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://control.msg91.com/api/v2/sendsms', [
            'sender' => config('constants.MSG91_SENDER_ID'),
            'route' => config('constants.MSG91_ROUTE'),
            'country' => config('constants.MSG91_COUNTRY'),
            'sms' => [
                [
                    'message' => $message,
                    'to' => [$mobile],
                    'template_id' => config('constants.MSG91_LOGIN_DLT'), // You can use any valid template here
                ]
            ]
        ]);

        return $response->json();
    }
}
