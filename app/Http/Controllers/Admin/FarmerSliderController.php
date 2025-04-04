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
                'image' => 'required|image|mimes:jpg,jpeg,png|max:25000',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $fileName = $image->getClientOriginalName();
        
                // Define destination path
                $destinationPath = public_path('slider_images');
        
                // Create the directory if it doesn't exist
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
        
                // Move the file
                $image->move($destinationPath, $fileName);
        
                $imagePath = 'slider_images/' . $fileName;
            }    

            FarmerSlider::create([
                'image' => $imagePath,
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
                    'image' => 'image|mimes:jpg,jpeg,png|max:25000',
                ]);
        
                // Delete old image if exists
                if ($slider->image && file_exists(public_path($slider->image))) {
                    unlink(public_path($slider->image));
                }
        
                $image = $request->file('image');
                $fileName = $image->getClientOriginalName();
                $destinationPath = public_path('slider_images');
        
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
        
                $image->move($destinationPath, $fileName);
                $slider->image = 'slider_images/' . $fileName;
            }

            $slider->save();

            Session::flash('success', 'Slider updated successfully!');
            return redirect()->route('farmer_slider.list');
        }

        public function deleteSlider($id)
        {
            $slider = FarmerSlider::findOrFail($id);
            Storage::disk('public')->delete($slider->image);
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
