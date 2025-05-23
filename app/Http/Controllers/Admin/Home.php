<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSidebar;
use App\Models\Farmer;
use App\Models\PaymentsReq;
use App\Models\Vendor;
use App\Models\Doctor;
use App\Models\Product;
use App\Models\ServiceRecordsTxn;
use App\Models\ServiceRecords;
use App\Models\Order;
use App\Models\DoctorRequest;
use App\Models\PaymentTransaction;
use App\Models\PaymentRequest;
use App\Models\SubscriptionBuy;
use App\Models\CheckMyFeedBuy;
use App\Models\Order1;
use App\Models\ServiceRecord;
use App\Models\ServiceRecordTxn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Home extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $data = [
            'user_name' => Auth::guard('admin')->user()->name,
            'sidebar_data' => AdminSidebar::all(),
            'farmer' => Farmer::count(),
            'vendor' => Vendor::where('is_approved', 1)->count(),
            'expert' => Doctor::where('is_approved', 1)->where('is_expert', 1)->count(),
            'normal' => Doctor::where('is_approved', 1)->where('is_expert', 0)->count(),
            'admin_product' => Product::where('is_admin', 1)->count(),
            'vendor_product' => Product::where('is_admin', 0)->where('is_approved', 1)->count(),
            'new' => Vendor::where('is_approved', 0)->count(),
            'service_report_weight_calculator' => ServiceRecordTxn::where('service', 'weight_calculator')->first(),
            'service_report' => ServiceRecord::first(),
            'total_orders' => Order1::whereIn('payment_status', [1, 2])->where('is_admin', 1)->count(),
            'new_orders' => Order1::whereIn('payment_status', [1, 2])->where('order_status', 1)->where('is_admin', 1)->count(),
            'today' => Order1::whereIn('payment_status', [1, 2])
                ->where('order_status', 1)
                ->where('is_admin', 1)
                ->whereDate('date', now()->format('Y-m-d'))
                ->count(),
            'accepted_orders' => Order1::whereIn('payment_status', [1, 2])->where('order_status', 2)->where('is_admin', 1)->count(),
            'dispatched_orders' => Order1::whereIn('payment_status', [1, 2])->where('order_status', 3)->where('is_admin', 1)->count(),
            'delivered_orders' => Order1::whereIn('payment_status', [1, 2])->where('order_status', 4)->where('is_admin', 1)->count(),
            'total_earning' => Order1::where('is_admin', 1)
                ->where('order_status', '!=', 6)
                ->whereIn('payment_status', [1, 2])
                ->sum('final_amount'),
            'total_cod_earning' => Order1::where('is_admin', 1)
                ->where('order_status', '!=', 6)
                ->where('payment_status', 2)
                ->sum('final_amount'),
            'total_v_orders' => Order1::where('is_admin', 0)
                ->where('payment_status', 1)
                ->sum('final_amount'),
            'total_d_orders' => DoctorRequest::where('payment_status', 1)->sum('fees'),
            'doctors_earning' => PaymentTransaction::whereNotNull('req_id')
                ->whereNotNull('doctor_id')
                ->sum('cr'),
            'vendor_earning' => PaymentTransaction::whereNotNull('req_id')
                ->whereNotNull('vendor_id')
                ->sum('cr'),
            'total_payments_processed_to_doctor' => PaymentsReq::whereNotNull('doctor_id')
                ->where('status', 1)
                ->sum('amount'),
            'total_payments_processed_to_vendor' => PaymentsReq::whereNotNull('vendor_id')
                ->where('status', 1)
                ->sum('amount'),
            'total_doctor_requests' => Doctor::where('is_approved', 0)->count(),
            'total_vendor_orders' => Order1::where('payment_status', 1)
                ->where('order_status', 4)
                ->where('is_admin', 0)
                ->count(),
            'subscriptions_purchased' => SubscriptionBuy::where('payment_status', 1)->sum('price'),
            'check_my_feed' => CheckMyFeedBuy::where('payment_status', 1)->sum('price'),
        ];

        return view('admin.dash', $data);
    }

    /**
     * Display service report based on type.
     */
    public function viewServiceReport(Request $request)
    {
        $type = $request->query('type');

        if (empty($type)) {
            return redirect()->route('admin.login');
        }

        $heading = '';
        $service_data = [];

        switch ($type) {
            case 'weight_calculator':
                $heading = 'Weight Calculator';
                $service_data = ServiceRecordTxn::where('service', 'weight_calculator')->get();
                break;
            case 'dmi_calculator':
                $heading = 'DMI Calculator';
                $service_data = ServiceRecordTxn::where('service', 'dmi_calculator')->get();
                break;
            case 'feed_calculator':
                $heading = 'Feed Calculator';
                $service_data = ServiceRecordTxn::where('service', 'feed_calculator')->get();
                break;
            case 'preg_calculator':
                $heading = 'Pregnancy Calculator';
                $service_data = ServiceRecordTxn::where('service', 'preg_calculator')->get();
                break;
            case 'silage_making':
                $heading = 'Silage Making';
                $service_data = ServiceRecordTxn::where('service', 'silage_making')->get();
                break;
            case 'animal_req':
                $heading = 'Animal Requirement';
                $service_data = ServiceRecordTxn::where('service', 'animal_req')->get();
                break;
            case 'pro_req':
                $heading = 'Project Requirement';
                $service_data = ServiceRecordTxn::where('service', 'pro_req')->get();
                break;
            default:
                return redirect()->route('admin.login');
        }

        return view('admin.subscription.view_service_reports', [
            'user_name' => Auth::guard('admin')->user()->name,
            'service_data' => $service_data,
            'heading' => $heading,
        ]);
    }
}