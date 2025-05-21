<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalePurchaseSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SalePurchaseSliderController extends Controller
{
    public function viewSlider()
    {
        $sliders = SalePurchaseSlider::all();
        return view('admin.salepurchaseslider.view_slider', [
            'user_name' => auth()->guard('admin')->user()->name,
            'slider_data' => $sliders
        ]);
    }

    public function addSlider()
    {
        return view('admin.salepurchaseslider.add_slider', [
            'user_name' => auth()->guard('admin')->user()->name
        ]);
    }

    public function addSliderData(Request $request, $t, $iw = null)
    {
        $validator = Validator::make($request->all(), [
            'image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'eq_image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:25000'
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
                $fileName = $cur_date->format('YmdHis') . '_' . $image->getClientOriginalName();
                $destinationPath = public_path('slider_images');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $image->move($destinationPath, $fileName);
                $imagePaths[] = 'slider_images/' . $fileName;
            }
        }

        $eqImagePaths = [];
        if ($request->hasFile('eq_image')) {
            foreach ($request->file('eq_image') as $eqImage) {
                $fileName = $cur_date->format('YmdHis') . '_' . $eqImage->getClientOriginalName();
                $destinationPath = public_path('slider_images');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $eqImage->move($destinationPath, $fileName);
                $eqImagePaths[] = 'slider_images/' . $fileName;
            }
        }

        $typ = base64_decode($t);
        if ($typ == 1) {
            $data = [
                'image' => json_encode($imagePaths),
                'eq_image' => json_encode($eqImagePaths),
                'ip' => $ip,
                'added_by' => $added_by,
                'is_active' => 1,
                'date' => $cur_date
            ];
            $slider = SalePurchaseSlider::create($data);
            $last_id = $slider->id;
        } elseif ($typ == 2) {
            $idw = base64_decode($iw);
            $slider = SalePurchaseSlider::findOrFail($idw);

            // Handle image updates
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

            // Handle eq_image updates
            if ($request->hasFile('eq_image')) {
                $existingEqImages = json_decode($slider->eq_image, true);
                if (is_array($existingEqImages)) {
                    foreach ($existingEqImages as $img) {
                        if (file_exists(public_path($img))) {
                            unlink(public_path($img));
                        }
                    }
                }
                $slider->eq_image = json_encode($eqImagePaths);
            }

            $last_id = $slider->save();
        }

        if ($last_id) {
            return redirect()->route('admin.salepurchaseslider.view')->with('smessage', 'Data inserted successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry error occurred');
    }

    public function updateSlider($idd)
    {
        $id = base64_decode($idd);
        $slider = SalePurchaseSlider::findOrFail($id);
        return view('admin.salepurchaseslider.update_slider', [
            'user_name' => auth()->guard('admin')->user()->name,
            'slider' => $slider,
            'id' => $idd
        ]);
    }

    // public function deleteSlider($idd)
    // {
    //     if (auth()->guard('admin')->user()->position !== 'Super Admin') {
    //         return view('errors.error500admin', ['e' => "Sorry You Don't Have Permission To Delete Anything."]);
    //     }

    //     $id = base64_decode($idd);
    //     $slider = SalePurchaseSlider::findOrFail($id);

    //     // Delete image files
    //     $images = json_decode($slider->image, true);
    //     if (is_array($images)) {
    //         foreach ($images as $img) {
    //             if (file_exists(public_path($img))) {
    //                 unlink(public_path($img));
    //             }
    //         }
    //     }

    //     // Delete eq_image files
    //     $eqImages = json_decode($slider->eq_image, true);
    //     if (is_array($eqImages)) {
    //         foreach ($eqImages as $img) {
    //             if (file_exists(public_path($img))) {
    //                 unlink(public_path($img));
    //             }
    //         }
    //     }

    //     $zapak = $slider->delete();
    //     if ($zapak) {
    //         return redirect()->route('admin.salepurchaseslider.view');
    //     }
    //     return "Error";
    // }

      public function destroy($idd)
{
    try {
        // Decode the base64 ID
        $id = base64_decode($idd);

        $slider = SalePurchaseSlider::findOrFail($id);

        // Delete associated image(s) if they exist
        $images = json_decode($slider->image, true);
        if (is_array($images)) {
            foreach ($images as $img) {
                if (file_exists(public_path($img))) {
                    unlink(public_path($img));
                }
            }
        }
        
         
        $eqImages = json_decode($slider->eq_image, true);
        if (is_array($eqImages)) {
            foreach ($eqImages as $img) {
                if (file_exists(public_path($img))) {
                    unlink(public_path($img));
                }
            }
        }
        // else (!empty($slider->image) && file_exists(public_path($slider->image))) {
        //     unlink(public_path($slider->image));
        // }

        $slider->delete();

        return redirect()->back()->with('smessage', 'Slider deleted successfully!');
    } catch (\Exception $e) {
        Log::error('Delete Slider Error: ' . $e->getMessage());
        return redirect()->back()->with('emessage', 'Something went wrong while deleting the slider.');
    }
}

    public function updateSliderStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $data_update = ['is_active' => $t === 'active' ? 1 : 0];
        $zapak = SalePurchaseSlider::where('id', $id)->update($data_update);

        if ($zapak) {
            return redirect()->route('admin.salepurchaseslider.view');
        }
        return view('errors.error500admin', ['e' => 'Error Occurred']);
    }
}