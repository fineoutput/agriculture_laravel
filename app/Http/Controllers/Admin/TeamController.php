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

class TeamController extends Controller
{
	public function admin_index(Request $req)
	{             $admin_id = $req->session()->get('admin_id');
			$services = json_decode($req->session()->get('services'));
			
			if (in_array(1, $services) || in_array(999, $services)) {
				
				return view('admin/index');
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
