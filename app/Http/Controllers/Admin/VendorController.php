<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function newVendors()
    {
        $vendor_data = Vendor::where('is_approved', 0)->orderBy('id', 'desc')->get();
        $heading = "New";
        return view('admin.vendor.view_vendor', compact('vendor_data', 'heading'));
    }

    public function acceptedVendors()
    {
        $vendor_data = Vendor::where('is_approved', 1)->orderBy('id', 'desc')->get();
        $heading = "Accepted";
        return view('admin.vendor.view_vendor', compact('vendor_data', 'heading'));
    }

    public function rejectedVendors()
    {
        $vendor_data = Vendor::where('is_approved', 2)->orderBy('id', 'desc')->get();
        $heading = "Rejected";
        return view('admin.vendor.view_vendor', compact('vendor_data', 'heading'));
    }

    public function addVendorData(Request $request, $t, $iw = "")
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'hi_name' => 'required|string',
            'pn_name' => 'required|string',
            'shop_name' => 'required|string',
            'shop_hi_name' => 'required|string',
            'shop_pn_name' => 'required|string',
            'address' => 'required|string',
            'hi_address' => 'required|string',
            'pn_address' => 'required|string',
            'district' => 'required|string',
            'hi_district' => 'required|string',
            'pn_district' => 'required|string',
            'city' => 'required|string',
            'hi_city' => 'required|string',
            'pn_city' => 'required|string',
            'state' => 'required|string',
            'pincode' => 'required|string',
            'gst_no' => 'nullable|string',
            'aadhar_no' => 'required|string',
            'pan_number' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $typ = base64_decode($t);
        $imagePath = null;

        $destinationPath = public_path('assets/uploads/vendor');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'vendor' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $imagePath = "assets/uploads/vendor/{$fileName}";
        }

        $data = [
            'name' => $request->name,
            'hi_name' => $request->hi_name,
            'pn_name' => $request->pn_name,
            'shop_name' => $request->shop_name,
            'shop_hi_name' => $request->shop_hi_name,
            'shop_pn_name' => $request->shop_pn_name,
            'address' => $request->address,
            'hi_address' => $request->hi_address,
            'pn_address' => $request->pn_address,
            'district' => $request->district,
            'hi_district' => $request->hi_district,
            'pn_district' => $request->pn_district,
            'city' => $request->city,
            'hi_city' => $request->hi_city,
            'pn_city' => $request->pn_city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'gst_no' => $request->gst_no,
            'aadhar_no' => $request->aadhar_no,
            'pan_number' => $request->pan_number,
            'phone' => $request->phone,
            'email' => $request->email,
            'ip' => $request->ip(),
            'added_by' => auth('admin')->id(),
            'is_active' => 1,
            'date' => now()->setTimezone('Asia/Kolkata'),
        ];

        if ($typ == 1) {
            if ($imagePath) $data['image'] = $imagePath;
            $last_id = Vendor::create($data);
        } elseif ($typ == 2) {
            $idw = base64_decode($iw);
            $vendor = Vendor::findOrFail($idw);
            $data['image'] = $imagePath ?? $vendor->image;
            unset($data['ip'], $data['added_by'], $data['is_active'], $data['date']); // Don't update these on edit
            $last_id = $vendor->update($data);
        } else {
            return redirect()->back()->with('emessage', 'Invalid operation type');
        }

        if ($last_id) {
            return redirect()->route('admin.vendor.accepted')->with('smessage', 'Data updated successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

  public function storeCodData(Request $request, $id)
{
    try {
        $vendor = Vendor::findOrFail($id);
        $cod = $request->has('cod') ? 1 : 0; // Checkbox checked = 1, unchecked = 0
        $vendor->update(['cod' => $cod]);

        return redirect()->back()->with('smessage', 'COD status updated successfully');
    } catch (\Exception $e) {
        Log::error('COD Update Error: ' . $e->getMessage());
        return redirect()->back()->with('emessage', 'Error updating COD status.');
    }
}


    public function deleteVendor(Request $request, $idd)
    {
        $id = base64_decode($idd);

        if (auth('admin')->user()->position !== 'Super Admin') {
            return redirect()->back()->with('emessage', 'Sorry, you don’t have permission to delete anything.');
        }

        $vendor = Vendor::find($id);
        if ($vendor) {
            $vendor->delete();
            return redirect()->back()->with('smessage', 'Data deleted successfully');
        }

        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function updateVendorStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $status = null;

        switch ($t) {
            case 'reject':
                $status = 2;
                $field = 'is_approved';
                break;
            case 'approve':
                $status = 1;
                $field = 'is_approved';
                break;
            case 'inactive':
                $status = 0;
                $field = 'is_active';
                break;
            case 'active':
                $status = 1;
                $field = 'is_active';
                break;
            default:
                return redirect()->back()->with('emessage', 'Invalid status type');
        }

        $updated = Vendor::where('id', $id)->update([$field => $status]);

        if ($updated) {
            return redirect()->back()->with('smessage', 'Status updated successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function updateVendor($idd)
    {
        $id = base64_decode($idd);
        $vendor = Vendor::findOrFail($id);
        $city_data = City::all();
        $state_data = State::all();
        return view('admin.vendor.update_vendor', compact('idd', 'vendor', 'city_data', 'state_data'));
    }

    public function setCommissionVendor($idd)
    {
        $id = base64_decode($idd);
        $vendor = Vendor::findOrFail($id);
        return view('admin.vendor.set_comission', compact('idd', 'vendor'));
    }

    public function addVendorData2(Request $request, $idd)
    {
        $validator = Validator::make($request->all(), [
            'set_comission' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $id = base64_decode($idd);
        $data = ['comission' => $request->set_comission];
        $updated = Vendor::where('id', $id)->update($data);

        if ($updated) {
            return redirect()->route('admin.vendor.accepted')->with('smessage', 'Data updated successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function qtyUpdate(Request $request)
    {
        $userId = $request->input('userId');
        $qtyDiscount = $request->input('qtyDiscount', '0'); // Default to '0' if empty

        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data received']);
        }

        $data = ['qty_discount' => $qtyDiscount];
        $updated = Vendor::where('id', $userId)->update($data);

        return response()->json([
            'status' => $updated ? 'success' : 'error',
            'message' => $updated ? 'Discount updated successfully' : 'Failed to update discount'
        ]);
    }
}