<?php


namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Msg91Service;
class SmsController extends Controller
{
     protected $msg91;
 public function __construct(Msg91Service $msg91)
    {
        $this->msg91 = $msg91;
    }

    public function sendTestSms()
    {
        $mobile = '9461937396'; // Replace with your test number
        $message = 'Your test OTP is 123456';

        $response = $this->msg91->sendTestSMS($mobile, $message);

        return response()->json($response);
    }
}
