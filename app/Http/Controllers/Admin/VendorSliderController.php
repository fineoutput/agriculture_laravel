<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorSlider;
use App\Models\VendorSliderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VendorSliderController extends Controller
{
    public function viewVendorSlider()
    {
        $sliders = VendorSlider::all();
        return view('admin.vendor_slider.view_vendorslider', [
            'user_name' => auth()->guard('admin')->user()->name,
            'vendorslider_data' => $sliders
        ]);
    }

    public function addVendorSlider()
    {
        return view('admin.vendor_slider.add_vendorslider', [
            'user_name' => auth()->guard('admin')->user()->name
        ]);
    }

    public function addVendorSliderData(Request $request, $t, $iw = null)
    {
        $validator = Validator::make($request->all(), [
            'image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:25000' // Allow multiple images
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $ip = $request->ip();
        $cur_date = now()->setTimezone('Asia/Kolkata');
        $added_by = auth()->guard('admin')->id();

        $imagePaths = [];
        if ($request->hasFile('image')) {
            foreach ($request->file('image') as $image) {
                $fileName = "vendorslider_" . $cur_date->format('YmdHis') . '_' . $image->getClientOriginalName();
                $destinationPath = public_path('slider_images');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $fileName);
                $imagePaths[] = 'slider_images/' . $fileName;
            }
        }

        $typ = base64_decode($t);
        if ($typ == 1) {
            $data = [
                'image' => json_encode($imagePaths),
                'ip' => $ip,
                'added_by' => $added_by,
                'is_active' => 1,
                'date' => $cur_date
            ];
            $slider = VendorSlider::create($data);
            $last_id = $slider->id;
        } elseif ($typ == 2) {
            $idw = base64_decode($iw);
            $slider = VendorSlider::findOrFail($idw);

            if ($request->hasFile('image')) {
                $existingImages = json_decode($slider->image, true);
                if (is_array($existingImages)) {
                    foreach ($existingImages as $img) {
                        if (file_exists(public_path($img))) {
                            unlink(public_path($img));
                        }
                    }
                }
                $slider->image = json_encode($imagePaths);
            }

            $last_id = $slider->save();
        }

        if ($last_id) {
            return redirect()->route('admin.vendorslider.view')->with('smessage', 'Data inserted successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry error occurred');
    }

    public function updateVendorSlider($idd)
    {
        $id = base64_decode($idd);
        $slider = VendorSlider::findOrFail($id);
        return view('admin.vendor_slider.update_vendorslider', [
            'user_name' => auth()->guard('admin')->user()->name,
            'vendorslider' => $slider,
            'id' => $idd
        ]);
    }

    // public function deleteVendorSlider($idd)
    // {
    //     if (auth()->guard('admin')->user()->position !== 'Super Admin') {
    //         return view('errors.error500admin', ['e' => "Sorry You Don't Have Permission To Delete Anything."]);
    //     }

    //     $id = base64_decode($idd);
    //     $slider = VendorSlider::findOrFail($id);

    //     $images = json_decode($slider->image, true);
    //     if (is_array($images)) {
    //         foreach ($images as $img) {
    //             if (file_exists(public_path($img))) {
    //                 unlink(public_path($img));
    //             }
    //         }
    //     }

    //     $zapak = $slider->delete();
    //     if ($zapak) {
    //         return redirect()->route('admin.vendorslider.view');
    //     }
    //     return "Error";
    // }

   
     public function destroy($idd)
{
    try {
        // Decode the base64 ID
        $id = base64_decode($idd);

        $slider = VendorSlider::findOrFail($id);

        // Delete associated image(s) if they exist
        $images = json_decode($slider->image, true);
        if (is_array($images)) {
            foreach ($images as $img) {
                if (file_exists(public_path($img))) {
                    unlink(public_path($img));
                }
            }
        } elseif (!empty($slider->image) && file_exists(public_path($slider->image))) {
            unlink(public_path($slider->image));
        }

        $slider->delete();

        return redirect()->back()->with('smessage', 'Slider deleted successfully!');
    } catch (\Exception $e) {
        Log::error('Delete Slider Error: ' . $e->getMessage());
        return redirect()->back()->with('emessage', 'Something went wrong while deleting the slider.');
    }
}
    public function updateVendorSliderStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $data_update = ['is_active' => $t === 'active' ? 1 : 0];
        $zapak = VendorSlider::where('id', $id)->update($data_update);

        if ($zapak) {
            return redirect()->route('admin.vendorslider.view');
        }
        return view('errors.error500admin', ['e' => 'Error Occurred']);
    }

    // Vendor Slider Request Functions
    public function viewVendorSliderRequest()
    {
        $sliders = VendorSliderRequest::all();
        return view('admin.vendor_slider.view_req_vendorslider', [
            'user_name' => auth()->guard('admin')->user()->name,
            'vendorslider_data' => $sliders
        ]);
    }

    public function updateVendorSliderRequest($idd, $t)
    {
        $id = base64_decode($idd);
        $data_update = ['is_active' => $t === 'active' ? 2 : 1]; // 2 for approved, 1 for pending
        $zapak = VendorSliderRequest::where('id', $id)->update($data_update);

        if ($zapak) {
            return redirect()->route('admin.vendorslider.view_request');
        }
        return view('errors.error500admin', ['e' => 'Error Occurred']);
    }
}