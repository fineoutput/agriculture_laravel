<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Msg91Service
{
    public function sendTestSMS($mobile, $message)
    {
        $response = Http::withHeaders([
            'authkey' => env('MSG91_AUTH_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://control.msg91.com/api/v2/sendsms', [
            'sender' => env('MSG91_SENDER_ID'),
            'route' => env('MSG91_ROUTE'),
            'country' => env('MSG91_COUNTRY'),
            'sms' => [
                [
                    'message' => $message,
                    'to' => [$mobile],
                    'template_id' => config('MSG91_LOGIN_DLT'), // You can use any valid template here
                ]
            ]
        ]);

        return $response->json();
    }
}
