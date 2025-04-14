<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubcategoryImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubcategoryImagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display all subcategory images.
     */
    public function viewSubcategoryImages()
    {
        $subcategory_images_data = SubcategoryImages::orderBy('id', 'desc')->get();
        Log::info('Subcategory Images Data:', $subcategory_images_data->toArray()); // Debug log

        return view('admin.subcategory_images.view_subcategoryimages', [
            'user_name' => Auth::guard('admin')->user()->name,
            'subcategory_images_data' => $subcategory_images_data,
        ]);
    }

    /**
     * Update subcategory images data.
     */
    public function addSubcategoryImagesData(Request $request, $t, $iw = null)
    {
        try {
            $idw = $iw ? base64_decode($iw) : null;
            $subcategory_image = $idw ? SubcategoryImages::findOrFail($idw) : null;

            // Validation rules
            $validator = Validator::make($request->all(), [
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
                'image_hindi' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
                'image_punjabi' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('emessage', $validator->errors()->first());
            }

            // Handle image uploads
            $image = $this->uploadImage($request, 'image', 'subcategory_images', $subcategory_image->image ?? null);
            $image_hindi = $this->uploadImage($request, 'image_hindi', 'subcategory_images', $subcategory_image->image_hindi ?? null, 'SubcategoryImagesHindi');
            $image_punjabi = $this->uploadImage($request, 'image_punjabi', 'subcategory_images', $subcategory_image->image_punjabi ?? null, 'SubcategoryImagesPunjabi');

            // Prepare data
            $data = [
                'image' => $image,
                'image_hindi' => $image_hindi,
                'image_punjabi' => $image_punjabi,
                'updated_at' => now(),
            ];

            // Update existing record
            if ($subcategory_image) {
                $subcategory_image->update($data);
                $success_message = 'Data updated successfully';
            } else {
                return redirect()->back()->with('emessage', 'Subcategory image not found');
            }

            return redirect()->route('admin.subcategory_images.view')->with('smessage', $success_message);

        } catch (\Exception $e) {
            Log::error('Subcategory Images Update Error: ' . $e->getMessage());
            return redirect()->back()->with('emessage', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for updating subcategory images.
     */
    public function updateSubcategoryImages($idd)
    {
        $id = base64_decode($idd);
        $subcategory_images = SubcategoryImages::findOrFail($id);

        return view('admin.subcategory_images.update_subcategoryimages', [
            'user_name' => Auth::guard('admin')->user()->name,
            'idd' => $idd,
            'subcategory_images' => $subcategory_images,
        ]);
    }

    /**
     * Helper method to handle image uploads.
     */
    private function uploadImage(Request $request, $field, $directory, $existing = null, $prefix = 'SubcategoryImages')
    {
        if ($request->hasFile($field) && $request->file($field)->isValid()) {
            $file = $request->file($field);
            $new_file_name = $prefix . date('YmdHis') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs("uploads/{$directory}", $new_file_name, 'public');

            // Delete old image if exists
            if ($existing && Storage::disk('public')->exists($existing)) {
                Storage::disk('public')->delete($existing);
            }

            return "storage/{$path}";
        }

        return $existing;
    }
}