<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DoctorSliderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function viewDoctorSlider()
    {
        $doctorslider_data = DoctorSlider::all();
        return view('admin.doctor_slider.view_doctorslider', compact('doctorslider_data'));
    }

    public function addDoctorSlider()
    {
        return view('admin.doctor_slider.add_doctorslider');
    }

    public function addDoctorSliderData(Request $request, $t, $iw = "")
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $typ = base64_decode($t);
        $imagePath = null;

        $destinationPath = public_path('assets/uploads/Doctorslider');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'doctorslider' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $imagePath = "assets/uploads/Doctorslider/{$fileName}";
        }

        $data = [
            'ip' => $request->ip(),
            'added_by' => auth('admin')->id(),
            'is_active' => 1,
            'date' => now()->setTimezone('Asia/Kolkata'),
        ];

        if ($typ == 1) {
            // Add new doctor slider
            if ($imagePath) $data['image'] = $imagePath;
            $last_id = DoctorSlider::create($data);
        } elseif ($typ == 2) {
            // Update existing doctor slider
            $idw = base64_decode($iw);
            $slider = DoctorSlider::findOrFail($idw);
            $data['image'] = $imagePath ?? $slider->image;
            unset($data['ip'], $data['added_by'], $data['is_active'], $data['date']); // Don't update these on edit
            $last_id = $slider->update($data);
        } else {
            return redirect()->back()->with('emessage', 'Invalid operation type');
        }

        if ($last_id) {
            return redirect()->route('admin.doctor_slider.view')->with('smessage', 'Data inserted successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function updateDoctorSlider($idd)
    {
        $id = base64_decode($idd);
        $doctorslider = DoctorSlider::findOrFail($id);
        return view('admin.doctor_slider.update_doctorslider', compact('idd', 'doctorslider'));
    }

    public function deleteDoctorSlider(Request $request, $idd)
    {
        $id = base64_decode($idd);

        if (auth('admin')->user()->position !== 'Super Admin') {
            return redirect()->back()->with('emessage', 'Sorry, you don’t have permission to delete anything.');
        }

        $slider = DoctorSlider::find($id);
        if ($slider) {
            // Optionally delete image here if needed
            $slider->delete();
            return redirect()->route('admin.doctor_slider.view');
        }

        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function updateDoctorSliderStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $is_active = $t === 'active' ? 1 : ($t === 'inactive' ? 0 : null);

        if (is_null($is_active)) {
            return redirect()->route('admin.doctor_slider.view')->with('emessage', 'Invalid status type');
        }

        $updated = DoctorSlider::where('id', $id)->update(['is_active' => $is_active]);

        if ($updated) {
            return redirect()->route('admin.doctor_slider.view');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }
}