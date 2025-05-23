<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\adminmodel\Team;
use App\adminmodel\AdminSidebar;
use App\adminmodel\AdminSidebar2;
use App\adminmodel\Order1Modal;
use App\adminmodel\UserModal;
use App\adminmodel\CategoryModal;
use App\adminmodel\ProductModal;
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
use Illuminate\Support\Facades\Auth;
class TeamController extends Controller
{
	public function admin_index(Request $req)
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
		$admin_id = $req->session()->get('admin_id');
			$services = json_decode($req->session()->get('services'));
			
			if (in_array(1, $services) || in_array(999, $services)) {
				
				
			} else {
				$service = AdminSidebar::where('id', $services[0])->first();
				if ($service->url == "#") {
					$serviceDetials = AdminSidebar2::where('main_id', $services[0])->first();
					return redirect()->route($serviceDetials->url);
				} else {
					return redirect()->route($service->url);
				}
			}
	}

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
	public function add_team_view(Request $req)
	{
			$service_data = AdminSidebar::get();
			return view('admin/team/add_team', compact('service_data'));
	}
	public function view_team(Request $req)
	{
			$Team_data = Team::wherenull('deleted_at')->orderBy('id', 'desc')->get();
			return view('admin/team/view_team', ['teamdetails' => $Team_data]);
	}
	public function UpdateTeamStatus($status, $idd, Request $req)
	{
			$id = base64_decode($idd);
			$admin_id = $req->session()->get('admin_id');
			$admin_position = $req->session()->get('position');
			if ($id == $admin_id) {
				return Redirect('/view_team')->with('error', "Sorry You can't change status of yourself.");
			}
			if ($admin_position == "Super Admin") {
				if ($status == "active") {
					$teamStatusInfo = [
						'is_active' => 1,
					];
					$TeamData = Team::wherenull('deleted_at')->where('id', $id)->first();
					$TeamData->update($teamStatusInfo);
				} else {
					$teamStatusInfo = [
						'is_active' => 0,
					];
					$TeamData = Team::wherenull('deleted_at')->where('id', $id)->first();
					$TeamData->update($teamStatusInfo);
				}
				return Redirect('/view_team')->with('success', 'Status Updated Successfully.');
			} else {
				return Redirect('/view_team')->with('error', "Sorry you dont have Permission to change admin, Only Super admin can change status.");
			}
	}
	public function deleteTeam($idd, Request $req)
	{
			$id = base64_decode($idd);
			$admin_id = $req->session()->get('admin_id');
			$admin_position = $req->session()->get('position');
			if ($id == $admin_id) {
				return Redirect('/view_team')->with('error', "Sorry You can't delete yourself.");
			}
			if ($admin_position == "Super Admin") {
				$TeamData = Team::wherenull('deleted_at')->where('id', $id)->first();
				if (!empty($TeamData)) {
					$img = $TeamData->image;
					$TeamData->delete();
					// if (!empty($img)) {
					// 	unlink($img);
					// }
					return Redirect('/view_team')->with('success', 'Data Deleted Successfully.');
				} else {
					return Redirect('/view_team')->with('error', 'Some Error Occurred.');
				}
			} else {
				return Redirect('/view_team')->with('error', "Sorry You Don't Have Permission To Delete Anything.");
			}
	}
	public function add_team_process(Request $req)
	{
			$admin_id = $req->session()->get('admin_id');
			$req->validate([
				'name' => 'required',
				'email' => 'required|unique:admin_teams|email',
				'password' => 'required',
				'power' => 'required '
			]);
			// dd($req);
			$service = $req->input('service');
			$services = $req->input('services');
			if ($service == 999) {
				$ser = '["999"]';
			} else {
				$ser = json_encode($services);
			}
			$fullimagepath = '';
			if (!empty($req->img)) {
				$allowedFormats = ['jpeg', 'jpg', 'webp'];
				$extension = strtolower($req->img->getClientOriginalExtension());
				if (in_array($extension, $allowedFormats)) {
					$file = time() . '.' . $req->img->extension();
					$req->img->move(public_path('uploads/image/Teams/'), $file);
					$fullimagepath = 'uploads/image/Teams/' . $file;
				} else {
					// Handle invalid file format (not allowed)
					return redirect()->back()->with('error', 'Invalid file format. Only jpeg, jpg, and webp files are allowed.');
				}
			}
			$teamInfo = [
				'name' => ucwords($req->input('name')),
				'email' => $req->input('email'),
				'phone' => $req->input('phone'),
				'password' => bcrypt($req->input('password')),
				'address' => $req->input('address'),
				'services' => $ser,
				'power' => $req->input('power'),
				'image' => $fullimagepath,
				'ip' => $req->ip(),
				'added_by' => $req->input('admin_id'),
				'is_active' => 1,
			];
			$last_id = Team::create($teamInfo);
			return Redirect('/view_team')->with('success', 'Data Added Successfully.');
		//return response()->json(['response' => 'OK']);
	}
	//
	// public function undoDelete(Request $req,$id){
	//
	// 		log::debug('$undoDelete');
	// 		//log::debug($admin_id);
	// 		//$admin = Admin::wherenull('deleted_at')-> where('admin_id', $admin_id)->first();
	// 		$admin = Admin::onlyTrashed()->find($id);
	// log::debug('$admin');
	// 		log::debug($admin);
	//         if (!is_null($admin)) {
	// log::debug('$notNull');
	//             $admin->restore();
	// 			 log::debug('$restorsucces');
	//             return Redirect('/adminList')->with('status', ' Deleted Admin Restored Successfully.');
	//         } else {
	//        log::debug('$restorfail');
	//
	// 			return Redirect('/adminList')->with('status', ' Deleted Admin Restored fail.');
	//         }
	//
	//
	//     }
	//
	// 	public function adminFilter(Request $req){
	//
	// 		$admin_name= $req->input('admin_name');
	// 		$country= $req->input('country_name');
	//
	// 		log::debug('$adminFilter');
	// 		//log::debug($admin_id);
	// 		if(($admin_name && $admin_name!="") || ($country== null && $country==""))
	// 		{
	// 			$admin = Admin::wherenull('deleted_at')-> where('name', $admin_name)->get();
	// 		}
	// 		elseif(($country && $country!="") || ($admin_name== null && $admin_name=="")){
	// 			$admin = Admin::wherenull('deleted_at')-> where('country', $country)->get();
	// 		}
	// 		else{
	// 			$admin="";
	// 		}
	// 		//$admins =  Admin::wherenull('deleted_at')->get();
	//
	//        //return Redirect('/adminList')->with('status', 'Admin Deleted Successfully.');
	//
	//     }
	
}
