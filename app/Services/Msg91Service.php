<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class Msg91Service
{
    public function sendSMS($mobile, $message)
    {
        $url = 'https://control.msg91.com/api/v2/sendsms'; // or flow endpoint

        $response = Http::withHeaders([
            'authkey' => config('constants.auth_key'),
            'Content-Type' => 'application/json',
        ])->post($url, [
            'sender' => config('constants.sender_id'),
            'route' => config('constants.route'),
            'country' => config('constants.country'),
            'sms' => [
                [
                    'message' => $message,
                    'to' => [$mobile],
                ],
            ],
        ]);

        return $response->json();
    }
}
