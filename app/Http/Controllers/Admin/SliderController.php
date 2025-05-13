<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        'image.*' => 'nullable|image|mimes:jpg,jpeg,png|max:25000'
    ]);

    if ($validator->fails()) {
        Log::error('Validation failed: ' . $validator->errors()->first());
        return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
    }

    $ip = $request->ip();
    $cur_date = now()->setTimezone('Asia/Kolkata');
    $added_by = auth()->guard('admin')->id();

    $imagePaths = [];
    if ($request->hasFile('image')) {
        $uploadPath = public_path('assets/uploads/slider');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        foreach ($request->file('image') as $image) {
            if ($image->isValid()) {
                // Generate a unique filename similar to CodeIgniter
                $fileName = 'slider' . $cur_date->format('YmdHis') . '_' . $image->getClientOriginalName();
                $destinationPath = $uploadPath;

                if ($image->move($destinationPath, $fileName)) {
                    // Store the full URL using asset()
                    $imagePaths[] = asset('assets/uploads/slider/' . $fileName);
                } else {
                    Log::error('Failed to move file: ' . $fileName);
                }
            } else {
                Log::error('Invalid file uploaded: ' . $image->getClientOriginalName());
            }
        }
    }

    Log::info('Image paths to store: ' . json_encode($imagePaths));

    $typ = base64_decode($t, true);
    if ($typ === false || !in_array($typ, [1, 2])) {
        return redirect()->back()->with('emessage', 'Invalid request type');
    }

    if ($typ == 1) {
        $data = [
            'image' => json_encode($imagePaths),
            'ip' => $ip,
            'added_by' => $added_by,
            'is_active' => 1,
            'date' => $cur_date
        ];
        $slider = Slider::create($data);
        $success = $slider->id ? true : false;
    } elseif ($typ == 2) {
        $idw = base64_decode($iw, true);
        if ($idw === false) {
            return redirect()->back()->with('emessage', 'Invalid slider ID');
        }
        $slider = Slider::findOrFail($idw);

        if (!empty($imagePaths)) {
            // Delete existing images only if new ones were uploaded
            $existingImages = json_decode($slider->image, true);
            if (is_array($existingImages)) {
                foreach ($existingImages as $img) {
                    // Extract the relative path from the full URL
                    $relativePath = str_replace(asset(''), '', $img);
                    $path = public_path($relativePath);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }
            $slider->image = json_encode($imagePaths);
        }

        $success = $slider->save();
    }

    if ($success) {
        return redirect()->route('admin.Slider.view')->with('smessage', 'Data ' . ($typ == 1 ? 'inserted' : 'updated') . ' successfully');
    }
    return redirect()->back()->with('emessage', 'Sorry, an error occurred');
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