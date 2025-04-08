<?php

    namespace App\Http\Controllers\Admin;
    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use App\Models\FarmerSlider;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Facades\Storage;

    class FarmerSliderController extends Controller
    {
        public function index()
        {
            if (!Auth::check()) {
                return redirect()->route('admin.login');
            }

            $sliders = FarmerSlider::all();
            // dd($sliders);

            return view('admin.farmer_slider.index', compact('sliders'));
        }

        public function createForm()
        {
            if (!Auth::check()) {
                return redirect()->route('admin.login');
            }

            return view('admin.farmer_slider.create');
        }

        public function storeSlider(Request $request)
{
    $request->validate([
        'image.*' => 'required|image|mimes:jpg,jpeg,png|max:25000', // validate each image
    ]);

    $imagePaths = [];

    if ($request->hasFile('image')) {
        foreach ($request->file('image') as $image) {
            $fileName = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('slider_images');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $image->move($destinationPath, $fileName);
            $imagePaths[] = 'slider_images/' . $fileName;
        }
    }

    FarmerSlider::create([
        'image' => json_encode($imagePaths),
        'ip' => request()->ip(),
        'added_by' => Auth::id(),
        'is_active' => 1,
    ]);

    Session::flash('success', 'Slider added successfully!');
    return redirect()->route('farmer_slider.list');
}


        public function editForm($id)
        {
            if (!Auth::check()) {
                return redirect()->route('admin.login');
            }

            $slider = FarmerSlider::findOrFail($id);
            return view('admin.farmer_slider.edit', compact('slider'));
        }

        public function updateSlider(Request $request, $id)
        {
            $slider = FarmerSlider::findOrFail($id);
        
            if ($request->hasFile('image')) {
                $request->validate([
                    'image.*' => 'image|mimes:jpg,jpeg,png|max:25000',
                ]);
        
                // Delete existing images
                if ($slider->image) {
                    $existingImages = json_decode($slider->image, true);
                    foreach ($existingImages as $img) {
                        if (file_exists(public_path($img))) {
                            unlink(public_path($img));
                        }
                    }
                }
        
                $uploadedImages = [];
        
                foreach ($request->file('image') as $image) {
                    $fileName = time() . '_' . $image->getClientOriginalName();
                    $destinationPath = public_path('slider_images');
        
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
        
                    $image->move($destinationPath, $fileName);
                    $uploadedImages[] = 'slider_images/' . $fileName;
                }
        
                // Store as JSON
                $slider->image = json_encode($uploadedImages);
            }
        
            $slider->save();
        
            Session::flash('success', 'Slider updated successfully!');
            return redirect()->route('farmer_slider.list');
        }
        

        public function deleteSlider($id)
{
    $slider = FarmerSlider::findOrFail($id);

    $images = json_decode($slider->image, true);
    if (is_array($images)) {
        foreach ($images as $img) {
            if (file_exists(public_path($img))) {
                unlink(public_path($img));
            }
        }
    } else {
        if (file_exists(public_path($slider->image))) {
            unlink(public_path($slider->image));
        }
    }

    $slider->delete();

    Session::flash('success', 'Slider deleted successfully!');
    return redirect()->route('farmer_slider.list');
}


        public function toggleSliderStatus($id)
        {
            $slider = FarmerSlider::findOrFail($id);
            $slider->is_active = !$slider->is_active;
            $slider->save();

            return redirect()->route('farmer_slider.list')->with('success', 'Slider status updated!');
        }
    }
