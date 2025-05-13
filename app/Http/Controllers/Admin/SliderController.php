<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    public function viewSlider()
    {
        $sliders = Slider::all();
        return view('admin.Slider.view_slider', [
            'user_name' => auth()->guard('admin')->user()->name,
            'slider_data' => $sliders
        ]);
    }

    public function addSlider()
    {
        return view('admin.Slider.add_slider', [
            'user_name' => auth()->guard('admin')->user()->name
        ]);
    }

    public function addSliderData(Request $request, $t, $iw = null)
    {
        $validator = Validator::make($request->all(), [
            'image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:25000' // Validate each image
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

        $typ = base64_decode($t);
        if ($typ == 1) {
            $data = [
                'image' => json_encode($imagePaths),
                'ip' => $ip,
                'added_by' => $added_by,
                'is_active' => 1,
                'date' => $cur_date
            ];
            $slider = Slider::create($data);
            $last_id = $slider->id;
        } elseif ($typ == 2) {
            $idw = base64_decode($iw);
            $slider = Slider::findOrFail($idw);

            if ($request->hasFile('image')) {
                // Delete existing images
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
            return redirect()->route('admin.Slider.view')->with('smessage', 'Data inserted successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry error occurred');
    }

    public function updateSlider($idd)
    {
        $id = base64_decode($idd);
        $slider = Slider::findOrFail($id);
        return view('admin.Slider.update_slider', [
            'user_name' => auth()->guard('admin')->user()->name,
            'slider' => $slider,
            'id' => $idd
        ]);
    }

    public function deleteSlider($idd)
    {
        if (auth()->guard('admin')->user()->position !== 'Super Admin') {
            return view('errors.error500admin', ['e' => "Sorry You Don't Have Permission To Delete Anything."]);
        }

        $id = base64_decode($idd);
        $slider = Slider::findOrFail($id);

        $images = json_decode($slider->image, true);
        if (is_array($images)) {
            foreach ($images as $img) {
                if (file_exists(public_path($img))) {
                    unlink(public_path($img));
                }
            }
        }

        $zapak = $slider->delete();
        if ($zapak) {
            return redirect()->route('admin.Slider.view');
        }
        return "Error";
    }

    public function updateSliderStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $data_update = ['is_active' => $t === 'active' ? 1 : 0];
        $zapak = Slider::where('id', $id)->update($data_update);

        if ($zapak) {
            return redirect()->route('admin.Slider.view');
        }
        return view('errors.error500admin', ['e' => 'Error Occurred']);
    }
}