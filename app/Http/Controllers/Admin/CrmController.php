<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\adminmodel\Team;
use App\Models\Crm_settings;
use App\adminmodel\AdminSidebar;
use App\adminmodel\AdminSidebar2;
use App\adminmodel\Order1Modal;
use App\adminmodel\UserModal;
use App\adminmodel\CategoryModal;
use App\adminmodel\ProductModal;

class CrmController extends Controller
{

    public function add_settings(Request $req)
    {

        return view('admin/crm/add_settings');
    }
  
    public function view_settings(Request $req)
	{
			$Team_data = Crm_settings::wherenull('deleted_at')->orderBy('id', 'desc')->get();
			return view('admin/crm/view_settings', ['settingsdetails' => $Team_data]);
	}
    public function add_settings_process(Request $req)
    {
        $admin_id = $req->session()->get('admin_id');

        // dd($admin_id);
        $vai =               $req->validate([
            'sitename' => 'required',
            'instagram_link' => 'required',
            'facebook_link' => 'required',
            'youtube_link' => 'required',


            'logo' => 'required ',

            'power' => 'required '
        ]);
        // dd($vai);
        // $service = $req->input('service');
        // $services = $req->input('services');
        // if ($service == 999) {
        // 	$ser = '["999"]';
        // } else {
        // 	$ser = json_encode($services);
        // }
        $fullImagePath = '';
        if ($req->hasFile('logo')) {
            $allowedFormats = ['jpeg', 'jpg', 'webp'];
            $extension = strtolower($req->file('logo')->getClientOriginalExtension());
            if (in_array($extension, $allowedFormats)) {
                $fileName = 'logo.' . $extension; // Keep the same name as the folder
                $req->file('logo')->move(public_path('uploads/image/Teams/'), $fileName);
                $fullImagePath = 'uploads/image/Teams/' . $fileName;
            } else {
                // Handle invalid file format (not allowed)
                return redirect()->back()->with('error', 'Invalid file format. Only jpeg, jpg, and webp files are allowed.');
            }
        }
        
        $data = [
            'sitename' => ucwords($req->input('sitename')),
            'instagram_link' => $req->input('instagram_link'),
            'facebook_link' => $req->input('facebook_link'),
            'youtube_link' => $req->input('youtube_link'),
            'address' => $req->input('address'),
            'phone' => $req->input('phone'),
            'power' => $req->input('power'),
            'added' => $admin_id,
            'logo' => $fullImagePath,
            'ip' => $req->ip(),


        ];
        $settings = new Crm_settings();
        $settings->fill($data); // Fill the model with the data
        $settings->save(); // Save the model to the database
        $last_id = $settings->id;
        return Redirect('/view_settings')->with('success', 'Data Added Successfully.');
        //return response()->json(['response' => 'OK']);
    }
    public function update_settings($id)
    {
        $datas = Crm_settings::findOrFail($id);
        return view('admin/crm/update_settings',['data' => $datas]);
        
    }

    public function update_settings_process(Request $req, $id)
    {
        $admin_id = $req->session()->get('admin_id');
        
        // Validate the request
      $vel =   $req->validate([
            'sitename' => 'required',
            'instagram_link' => 'required',
            'facebook_link' => 'required',
            'youtube_link' => 'required',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Update validation rules for logo
            'power' => 'required '
        ]);
    
        // dd($vel);
        // Find the settings by ID
        $settings = Crm_settings::findOrFail($id);
    
        // Handle the logo update
        if ($req->hasFile('logo')) {
            $allowedFormats = ['jpeg', 'jpg', 'webp'];
            $extension = strtolower($req->file('logo')->getClientOriginalExtension());
            if (in_array($extension, $allowedFormats)) {
                $fileName = 'logo.' . $extension;
                $req->file('logo')->move(public_path('uploads/image/Teams/'), $fileName);
                $settings->logo = 'uploads/image/Teams/' . $fileName;
            } else {
                // Handle invalid file format (not allowed)
                return redirect()->back()->with('error', 'Invalid file format. Only jpeg, jpg, and webp files are allowed.');
            }
        }
    
        // Update other fields
        $settings->sitename = ucwords($req->input('sitename'));
        $settings->instagram_link = $req->input('instagram_link');
        $settings->facebook_link = $req->input('facebook_link');
        $settings->youtube_link = $req->input('youtube_link');
        $settings->address = $req->input('address');
        $settings->phone = $req->input('phone');
        $settings->power = $req->input('power');
        $settings->added_by = $admin_id;
        $settings->ip = $req->ip();
    
        // Save the updated settings
        $settings->save();
    
        return redirect('/view_settings')->with('success', 'Data Updated Successfully.');
    }

    
    public function deletesetting($idd, Request $req)
	{
			$id = base64_decode($idd);
			$admin_id = $req->session()->get('admin_id');
			$admin_position = $req->session()->get('position');
			if ($id == $admin_id) {
				return Redirect('/view_settings')->with('error', "Sorry You can't delete yourself.");
			}
			if ($admin_position == "Super Admin") {
				$Data = Crm_settings::wherenull('deleted_at')->where('id', $id)->first();
				if (!empty($Data)) {
					$img = $Data->image;
					$Data->delete();
					if (!empty($img)) {
						unlink($img);
					}
					return Redirect('/view_settings')->with('success', 'Data Deleted Successfully.');
				} else {
					return Redirect('/view_settings')->with('error', 'Some Error Occurred.');
				}
			} else {
				return Redirect('/view_settings')->with('error', "Sorry You Don't Have Permission To Delete Anything.");
			}
	}
    
}
