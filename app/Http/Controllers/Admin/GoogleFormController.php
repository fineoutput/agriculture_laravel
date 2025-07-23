<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoogleForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GoogleFormController extends Controller
{
    public function users()
{
    // if (Session::has('admin_data')) {
        $data['user_name'] = Session::get('user_name'); // Or wherever you're storing it

        // Using Eloquent Models
        $data['form_data'] = GoogleForm ::orderBy('id', 'DESC')->get();
    return view('admin.googleform.users', $data);

        
    // } else {
    //     redirect()->back()->with('emessage', 'Sorry, an error occurred'); 
    // return view('admin.googleform.users');

    // }
}

public function accept($id)
{
    $form = GoogleForm::findOrFail($id);
    $form->status = 1;
    $form->save();

    return redirect()->back()->with('message', 'Form accepted successfully.');
}

public function reject($id)
{
    $form = GoogleForm::findOrFail($id);
    $form->status = 2;
    $form->save();

    return redirect()->back()->with('message', 'Form rejected successfully.');
}

public function disqualify($id)
{
    $form = GoogleForm::findOrFail($id);

    if ($form->status != 1) {
        return redirect()->back()->with('emessage', 'Only accepted users can be disqualified.');
    }

    $form->status = 3;
    $form->save();

    return redirect()->back()->with('message', 'User disqualified successfully.');
}

}
