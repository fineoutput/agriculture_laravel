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

    public function sendTestSMS()
    {
        $mobile = '9461937396'; // include country code
        $message = 'Hello! Your test SMS from MSG91 is working.';

        $response = $this->msg91->sendSMS($mobile, $message);

        return response()->json($response);
    }
}
