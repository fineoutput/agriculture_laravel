<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Redirect;
use Laravel\Sanctum\PersonalAccessToken;
use DateTime;


class HomeController extends Controller
{
    // ============================= START INDEX ============================ 
    public function index(Request $req)
    {
     
        return view('welcome')->withTitle('');
    }
    public function about(Request $req)
    {
     
        return view('about')->withTitle('');
    }
    public function contact(Request $req)
    {
     
        return view('contact')->withTitle('');
    }
    
    public function doctor(Request $req)
    {
     
        return view('doctor')->withTitle('');
    }
    public function farmer(Request $req)
    {
     
        return view('farmer')->withTitle('');
    }
    public function gallery(Request $req)
    {
     
        return view('gallery')->withTitle('');
    }
    public function privacy_policy(Request $req)
    {
     
        return view('privacy_policy')->withTitle('');
    }
    public function privacy(Request $req)
    {
     
        return view('privacy')->withTitle('');
    }
    public function refund (Request $req)
    {
     
        return view('refund-cancellation-policy')->withTitle('');
    }
    public function services (Request $req)
    {
     
        return view('services')->withTitle('');
    }
    public function shipping_delivery (Request $req)
    {
     
        return view('shipping_delivery')->withTitle('');
    }
    public function terms_and_conditions (Request $req)
    {
     
        return view('terms_and_conditions')->withTitle('');
    }
    public function vendor (Request $req)
    {
     
        return view('vendor')->withTitle('');
    }
}
