<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\DoctorRequest;
use App\Models\PaymentTransaction;
use App\Models\State;
use App\Models\ExpertiseCategory;
use Carbon\Carbon;

class DoctorsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    // public function newDoctors()
    // {
    //     $doctors = Doctor::where('is_approved', 0)
    //         ->orderBy('id', 'desc')
    //         ->get();
    //         dd($doctors);
    //     return view('admin.doctor.view_doctor', [
    //         'user_name' => auth()->user()->name,
    //         'doctor_data' => $doctors,
    //         'heading' => 'New Doctors'
    //     ]);
    // }

    public function newDoctors()
    {
        $doctors = Doctor::all(); // Fetch all without any conditions
        // dd($doctors);
    
        return view('admin.doctor.view_doctor', [
            'user_name' => auth()->user()->name,
            'doctors' => $doctors,
            'heading' => 'New Doctors'
        ]);
    }

    public function acceptedDoctors()
    {
        $doctors = Doctor::where('is_approved', 1)
            ->where('is_expert', true)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.doctor.view_expert_doctor', [
            'user_name' => auth()->user()->name,
            'doctors' => $doctors,
            'heading' => 'Accepted'
        ]);
    }

    public function normalDoctors()
    {
        $doctors = Doctor::where('is_approved', 1)
            ->where('is_expert', false)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.doctor.view_normal_doctor', [
            'user_name' => auth()->user()->name,
            'doctors' => $doctors,
            'heading' => 'Accepted'
        ]);
    }

    public function totalDoctors()
    {
        $doctors = Doctor::where('is_approved', 1)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.doctor.total_doctor', [
            'user_name' => auth()->user()->name,
            'doctors' => $doctors,
            'heading' => 'Accepted'
        ]);
    }

    public function rejectedDoctors()
    {
        $doctors = Doctor::where('is_approved', 2)
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.doctor.view_doctor', [
            'user_name' => auth()->user()->name,
            'doctors' => $doctors,
            'heading' => 'Rejected'
        ]);
    }

    public function viewPdf($idd)
    {
        $id = base64_decode($idd);
        $request_data = DoctorRequest::where('payment_status', 1)
            ->where('id', $id)
            ->firstOrFail();

        return view('admin.doctor.view_pdf', [
            'user_name' => auth()->user()->name,
            'data' => $request_data
        ]);
    }

    public function doctorRequest()
    {
        $requests = DoctorRequest::where('payment_status', 1)
            ->with('paymentTransactions')
            ->orderBy('id', 'desc')
            ->get();

        $count = $requests->sum(function ($request) {
            $ptx = $request->paymentTransactions->first();
            return $ptx && $ptx->cr ? ($request->fees - $ptx->cr) : 0;
        });

        return view('admin.doctor.view_doctor_req', [
            'user_name' => auth()->user()->name,
            'request_data' => $requests,
            'count' => $count
        ]);
    }

    public function deleteDoctor($idd)
    {
        if (auth()->user()->position !== 'Super Admin') {
            return view('errors.error500admin', ['e' => "Sorry You Don't Have Permission To Delete Anything."]);
        }

        $id = base64_decode($idd);
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();

        return redirect()->back()->with('smessage', 'Data deleted successfully');
    }

    public function updateDoctorStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $doctor = Doctor::findOrFail($id);

        switch ($t) {
            case 'reject':
                $doctor->is_approved = 2;
                break;
            case 'approve':
                $doctor->is_approved = 1;
                break;
            case 'inactive':
                $doctor->is_active = false;
                break;
            case 'active':
                $doctor->is_active = true;
                break;
            case 'normal':
                $doctor->is_expert = false;
                break;
            case 'expert':
                $doctor->is_expert = true;
                break;
        }

        $doctor->save();

        return redirect()->back()->with('smessage', 'Status updated successfully');
    }

    public function updateDoctor($idd)
    {
        $id = base64_decode($idd);
        $doctor = Doctor::findOrFail($id);
        $states = State::all();
        $expert_categories = ExpertiseCategory::where('is_active', true)->get();

        return view('admin.doctor.edit_doctor', [
            'user_name' => auth()->user()->name,
            'id' => $idd,
            'doctor' => $doctor,
            'states' => $states,
            'expert_categories' => $expert_categories
        ]);
    }

    public function setCommissionDoctor($idd)
    {
        $id = base64_decode($idd);
        $doctor = Doctor::findOrFail($id);

        return view('admin.doctor.set_comission', [
            'user_name' => auth()->user()->name,
            'id' => $idd,
            'doctor' => $doctor
        ]);
    }

    public function addDoctorData2(Request $request, $idd)
    {
        $request->validate([
            'set_commission' => 'required|numeric',
            'fees' => 'required|numeric'
        ]);

        $id = base64_decode($idd);
        $doctor = Doctor::findOrFail($id);
        
        $doctor->update([
            'commission' => $request->set_commission,
            'fees' => $request->fees
        ]);

        return redirect()->route('admin.doctor.accepted')->with('smessage', 'Data updated successfully');
    }

    public function addFeesDoctor($y)
    {
        $id = base64_decode($y);
        $doctor = Doctor::findOrFail($id);

        return view('admin.doctor.add_fees', [
            'user_name' => auth()->user()->name,
            'id' => $y,
            'doctor' => $doctor
        ]);
    }

    public function addDoctorData3(Request $request, $y)
    {
        $request->validate([
            'fees' => 'nullable|numeric',
            'expertise' => 'nullable|string'
        ]);

        $id = base64_decode($y);
        $doctor = Doctor::findOrFail($id);
        
        $doctor->update([
            'fees' => $request->fees,
            'expertise' => $request->expertise,
            'is_active2' => true
        ]);

        return redirect()->route('admin.doctor.view')->with('smessage', 'Data updated successfully');
    }

    public function updateDoctorData(Request $request, $y)
    {
        $request->validate([
            'name' => 'required|string',
            'hi_name' => 'required|string',
            'pn_name' => 'required|string',
            'email' => 'required|email',
            'type' => 'required|string',
            'degree' => 'nullable|string',
            'experience' => 'nullable|string',
            'district' => 'required|string',
            'hi_district' => 'required|string',
            'pn_district' => 'required|string',
            'state' => 'required|string',
            'city' => 'required|string',
            'hi_city' => 'required|string',
            'pn_city' => 'required|string',
            'pincode' => 'required|string',
            'aadhar_no' => 'required|string',
            'expert_category' => 'required|array',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000'
        ]);

        $id = base64_decode($y);
        $doctor = Doctor::findOrFail($id);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = 'doctor_' . Carbon::now()->format('YmdHis') . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('doctor_images');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $fileName);
            $doctor->image = 'doctor_images/' . $fileName;
        }

        $doctor->update([
            'name' => $request->name,
            'hi_name' => $request->hi_name,
            'pn_name' => $request->pn_name,
            'email' => $request->email,
            'type' => $request->type,
            'degree' => $request->degree,
            'experience' => $request->experience,
            'district' => $request->district,
            'hi_district' => $request->hi_district,
            'pn_district' => $request->pn_district,
            'state' => $request->state,
            'city' => $request->city,
            'hi_city' => $request->hi_city,
            'pn_city' => $request->pn_city,
            'pincode' => $request->pincode,
            'aadhar_no' => $request->aadhar_no,
            'expert_category' => $request->expert_category,
        ]);

        return redirect()->route('admin.doctor.accepted')->with('smessage', 'Data updated successfully');
    }
}