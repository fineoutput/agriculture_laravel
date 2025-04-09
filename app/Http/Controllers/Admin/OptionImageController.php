<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OptionImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OptionImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function viewSlider()
    {
        $slider_data = OptionImage::all();
        return view('admin.option_image.view_slider', compact('slider_data'));
    }

    public function addSlider()
    {
        return view('admin.option_image.add_slider');
    }

    public function addSliderData(Request $request, $t, $iw = "")
    {
        $validator = Validator::make($request->all(), [
            'image1' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'image2' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'image3' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'image4' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $typ = base64_decode($t);
        $destinationPath = public_path('assets/uploads/slider');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $imageFields = ['image1', 'image2', 'image3', 'image4'];
        $imagePaths = [];

        foreach ($imageFields as $index => $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $fileName = "category" . now()->format('YmdHis') . ($index + 1) . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $fileName);
                $imagePaths[$field] = "assets/uploads/slider/{$fileName}";
                Log::info("{$field} uploaded successfully: " . $imagePaths[$field]);
            }
        }

        $data = [
            'date' => now()->setTimezone('Asia/Kolkata'),
        ];

        if ($typ == 1) {
            // Add new slider
            $data = array_merge($data, array_filter($imagePaths));
            $last_id = OptionImage::create($data);
            Log::info('Data inserted with ID: ' . $last_id->id);
        } elseif ($typ == 2) {
            // Update existing slider
            $idw = base64_decode($iw);
            $slider = OptionImage::findOrFail($idw);
            $data['image1'] = $imagePaths['image1'] ?? $slider->image1;
            $data['image2'] = $imagePaths['image2'] ?? $slider->image2;
            $data['image3'] = $imagePaths['image3'] ?? $slider->image3;
            $data['image4'] = $imagePaths['image4'] ?? $slider->image4;
            unset($data['date']); // Don't update date on edit
            $last_id = $slider->update($data);
            Log::info('Update result: ' . ($last_id ? 'Success' : 'Failed'));
        } else {
            return redirect()->back()->with('emessage', 'Invalid operation type');
        }

        if ($last_id) {
            return redirect()->route('admin.option_image.view')->with('smessage', 'Data inserted successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function updateSlider($idd)
    {
        $id = base64_decode($idd);
        $slider = OptionImage::findOrFail($id);
        return view('admin.option_image.update_slider', compact('idd', 'slider'));
    }

    public function deleteSlider(Request $request, $idd)
    {
        $id = base64_decode($idd);

        if (auth('admin')->user()->position !== 'Super Admin') {
            return redirect()->back()->with('emessage', 'Sorry, you don’t have permission to delete anything.');
        }

        $slider = OptionImage::find($id);
        if ($slider) {
            // Optionally delete images here if needed
            $slider->delete();
            return redirect()->route('admin.option_image.view');
        }

        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function updateSliderStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $is_active = $t === 'active' ? 1 : ($t === 'inactive' ? 0 : null);

        if (is_null($is_active)) {
            return redirect()->route('admin.option_image.view')->with('emessage', 'Invalid status type');
        }

        $updated = OptionImage::where('id', $id)->update(['is_active' => $is_active]);

        if ($updated) {
            return redirect()->route('admin.option_image.view');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }
}