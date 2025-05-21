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
        Log::info('Request data: ' . json_encode($request->all()));
        Log::info('Request files: ' . json_encode($request->file()));

        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . json_encode($validator->errors()->all()));
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $ip = $request->ip();
        $cur_date = now()->setTimezone('Asia/Kolkata');
        $added_by = auth()->guard('admin')->id();

        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $uploadPath = public_path('assets/uploads/slider');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
                Log::info('Created directory: ' . $uploadPath);
            }

            $image = $request->file('image');
            $fileName = 'slider' . $cur_date->format('YmdHis') . '_' . $image->getClientOriginalName();
            $destinationPath = $uploadPath;

            try {
                if ($image->move($destinationPath, $fileName)) {
                    $imagePath = 'assets/uploads/slider/' . $fileName; // Store relative path
                    Log::info('File uploaded: ' . $fileName);
                } else {
                    Log::error('Failed to move file: ' . $fileName);
                }
            } catch (\Exception $e) {
                Log::error('Error moving file: ' . $fileName . ' - ' . $e->getMessage());
            }
        } else {
            Log::warning('No valid image file found in request');
        }

        Log::info('Image path to store: ' . $imagePath);

        $typ = base64_decode($t, true);
        if ($typ === false || !in_array($typ, [1, 2])) {
            return redirect()->back()->with('emessage', 'Invalid request type');
        }

        if ($typ == 1) {
            $data = [
                'image' => $imagePath,
                'ip' => $ip,
                'added_by' => $added_by,
                'is_active' => 1,
                'date' => $cur_date
            ];
            Log::info('Creating slider with data: ' . json_encode($data));
            try {
                $slider = Slider::create($data);
                $success = $slider->id ? true : false;
            } catch (\Exception $e) {
                Log::error('Failed to create slider: ' . $e->getMessage());
                return redirect()->back()->with('emessage', 'Failed to save slider data');
            }
        } elseif ($typ == 2) {
            $idw = base64_decode($iw, true);
            if ($idw === false) {
                return redirect()->back()->with('emessage', 'Invalid slider ID');
            }
            $slider = Slider::findOrFail($idw);

            if ($imagePath) {
                // Delete existing image if new one is uploaded
                if ($slider->image) {
                    $path = public_path($slider->image);
                    if (file_exists($path)) {
                        unlink($path);
                        Log::info('Deleted old image: ' . $path);
                    }
                }
                $slider->image = $imagePath;
            }

            Log::info('Updating slider with image: ' . $imagePath);
            try {
                $success = $slider->save();
            } catch (\Exception $e) {
                Log::error('Failed to update slider: ' . $e->getMessage());
                return redirect()->back()->with('emessage', 'Failed to update slider data');
            }
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

    // public function deleteSlider($idd)
    // {
    //     if (auth()->guard('admin')->user()->position !== 'Super Admin') {
    //         return view('errors.error500admin', ['e' => "Sorry You Don't Have Permission To Delete Anything."]);
    //     }

    //     $id = base64_decode($idd);
    //     $slider = Slider::findOrFail($id);

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
    //         return redirect()->route('admin.Slider.view');
    //     }
    //     return "Error";
    // }

    public function destroy($idd)
{
    try {
        // Decode the base64 ID
        $id = base64_decode($idd);

        $slider = Slider::findOrFail($id);

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