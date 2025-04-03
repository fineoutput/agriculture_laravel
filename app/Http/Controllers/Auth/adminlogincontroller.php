<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\adminmodel\Team;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class adminlogincontroller extends Controller
{

  function admin_login(Request $req)
  {
    return view('admin/login/index');
  }



  //login Admin
  public function admin_login_process(Request $req)
  {
    $validator = Validator::make($req->all(),[
      'email'=> 'required|email',
      'password' => 'required'
  ]);
  if($validator->passes()){
       if (Auth::guard('admin')->attempt(['email' => $req->email, 'password' => $req->password], $req->get('remember'))){

           $admin = Auth::guard('admin')->user();
          if($admin->is_active==1){

            $db_id = $admin->id;
            $db_name = $admin->name;
            $db_image = $admin->image;
            $db_power = $admin->power;
            $db_services = $admin->services;
  
            if ($db_power == 1) {
              $pos = "Super Admin";
            }
            if ($db_power == 2) {
              $pos = "Admin";
            }
            if ($db_power == 3) {
              $pos = "Manager";
            }
            
            $req->session()->put('admin_name', $db_name);
            $req->session()->put('admin_image', $db_image);
            $req->session()->put('power', $db_power);
            $req->session()->put('services', $db_services);
            $req->session()->put('position', $pos);
            $req->session()->put('admin_id', $db_id);

            return Redirect()->route('admin_index')->with('success', 'Login Successfully.');

          }
          else{
              Auth::guard('admin')->logout();
              return redirect()->route('admin_login')->with('error','You are not authorized to access admin pannel.');
          }
      }
      else{
          return redirect()->back()->with('error', 'Invalid Email/Password!');
      }
  }

  else{
      return redirect()->back()
      ->withErrors($validator)
      ->withInput($req->only('email'));
  }
  
  }



  //logout Admin function
  public function admin_logout(Request $req)
  {
    Auth::guard('admin')->logout();

      $req->session()->forget('admin_name');
      $req->session()->forget('admin_image');
      $req->session()->forget('power');
      $req->session()->forget('services');
      $req->session()->forget('position');
      $req->session()->forget('admin_id');

      return Redirect()->route('admin_login')->with('success', 'Logout Successfully.');
    } 

  // admin profile data

  function admin_profile(Request $req)
  {
      $admin_id = $req->session()->get('admin_id');
      // die();
      $profile_data = Team::wherenull('deleted_at')->where('is_active', 1)->where('id', $admin_id)->first();

      return view('admin/profile/view_profile', compact('profile_data'));
   
  }


  //view admin change Password
  function admin_change_pass_view(Request $req)
  {
      $admin_id = $req->session()->get('admin_id');

      return view('admin/profile/change_password');
  }



  //change Admin password
  public function admin_change_password(Request $req)
  {
      $req->validate([
        'old' => 'required',
        'new' => 'required',
        'confirm' => 'required'

      ]);


      $old = $req->input('old');
      $old_enc = bcrypt($old);
      echo $old_enc;
      exit;
      $new = $req->input('new');
      $confirm = $req->input('confirm');
      $admin_id = $req->session()->get('admin_id');

      $team_da = Team::wherenull('deleted_at')->where('is_active', 1)->where('id', $admin_id)->first();

      if (!empty($team_da)) {

        if ($confirm == $new) {

          if ($team_da->password == $old_enc) {

            //update password
            $teamUpdateInfo = [
              'password' => bcrypt($new),
            ];


            $TeamData = Team::wherenull('deleted_at')->where('id', $admin_id)->first();

            $TeamData->update($teamUpdateInfo);

            return redirect()->back()->with('success', 'Password Successfully Changed!');
          } else {

            return redirect()->back()->with('error', 'Invalid Old Password!');
          }
        } else {

          return redirect()->back()->with('error', 'New And Confirm Password Does Not Matched!');
        }
      }
  }
}
