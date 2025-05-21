<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manager;
use App\Models\Farmer;
use App\Models\Doctor;
use App\Models\Vendor;
use App\Models\AdminSidebar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    public function addManager()
    {
        $side = AdminSidebar::all();
        return view('admin.manager.add_manager', compact('side'));
    }

    public function addManagerData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp|max:25000',
            'aadhar' => 'nullable|string|max:255',
            'refer_code' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            $destinationPath = public_path('assets/uploads/manager');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            foreach ($request->file('images') as $index => $image) {
                $fileName = "team_" . now()->format('YmdHis') . "_{$index}." . $image->getClientOriginalExtension();
                $image->move($destinationPath, $fileName);
                $imagePaths[] = "assets/uploads/manager/{$fileName}";
            }
        }

        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'email' => $request->email,
            'images' => json_encode($imagePaths), // Store as JSON instead of serialize
            'aadhar' => $request->aadhar,
            'refer_code' => $request->refer_code,
            'ip' => $request->ip(),
            'is_active' => 1,
            'added_by' => auth('admin')->id(),
            'date' => now()->setTimezone('Asia/Kolkata'),
        ];

        $manager = Manager::create($data);

        if ($manager) {
            return redirect()->route('admin.manager.view')->with('smessage', 'Manager has been successfully added');
        }
        return redirect()->back()->with('emessage', 'Error occurred in data insertion, please try again');
    }

    public function viewManager()
    {
        $manager_data = Manager::all();
        return view('admin.manager.view_manager', compact('manager_data'));
    }

    public function viewFarmers($idd)
    {
        $id = base64_decode($idd);
        $farmers_data = Farmer::where('refer_code', $id)->get();
        return view('admin.farmers.view_farmers', compact('farmers_data'));
    }

    public function viewDoctors($idd)
    {
        $id = base64_decode($idd);
        $doctor_data = Doctor::where('refer_code', $id)->get();
        return view('admin.doctor.view_doctor', compact('doctor_data'));
    }

    public function viewVendors($idd)
    {
        $id = base64_decode($idd);
        $vendor_data = Vendor::where('refer_code', $id)->get();
        $heading = 'New';
        return view('admin.vendor.view_vendor', compact('vendor_data', 'heading'));
    }

    public function updateManagerStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $adminId = auth('admin')->id();

        // if ($id == $adminId) {
        //     return redirect()->back()->with('emessage', "Sorry, you can't change status of yourself");
        // }

        // if (auth('admin')->user()->position !== 'Super Admin') {
        //     return redirect()->back()->with('emessage', 'Sorry, you don’t have permission to change admin status. Only Super Admin can change status');
        // }

        $is_active = $t === 'active' ? 1 : 0;
        $updated = Manager::where('id', $id)->update(['is_active' => $is_active]);

        if ($updated) {
            return redirect()->route('admin.manager.view')->with('smessage', 'Status successfully updated');
        }
        return redirect()->back()->with('emessage', 'Error occurred');
    }

    public function deleteManager($idd)
    {
        $id = base64_decode($idd);
        // $adminId = auth('admin')->id();

        // if (auth('admin')->user()->position !== 'Super Admin') {
        //     return redirect()->back()->with('emessage', "Sorry, you don't have permission to delete anything");
        // }

        $manager = Manager::findOrFail($id);
        $images = json_decode($manager->images, true);
        if (is_array($images)) {
            foreach ($images as $image) {
                $filePath = public_path($image);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        $deleted = $manager->delete();

        if ($deleted) {
            return redirect()->back()->with('smessage', 'Successfully deleted');
        }
        return redirect()->back()->with('emessage', 'Error occurred');
    }
}