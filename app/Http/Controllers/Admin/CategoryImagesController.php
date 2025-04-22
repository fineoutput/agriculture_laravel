<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryImagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display all category images.
     */
    public function viewCategoryImages()
    {
        $category_images_data = CategoryImages::orderBy('id', 'desc')->get();
        Log::info('Category Images Data:', $category_images_data->toArray()); // Debug log

        return view('admin.category_images.view_categoryimages', [
            'user_name' => Auth::guard('admin')->user()->name,
            'category_images_data' => $category_images_data,
        ]);
    }

    /**
     * Update category images data.
     */
    public function addCategoryImagesData(Request $request, $t, $iw = null)
    {
        try {
            $idw = $iw ? base64_decode($iw) : null;
            $category_image = $idw ? CategoryImages::findOrFail($idw) : null;

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
            $image = $this->uploadImage($request, 'image', 'category_images', $category_image->image ?? null);
            $image_hindi = $this->uploadImage($request, 'image_hindi', 'category_images', $category_image->image_hindi ?? null, 'CategoryImagesHindi');
            $image_punjabi = $this->uploadImage($request, 'image_punjabi', 'category_images', $category_image->image_punjabi ?? null, 'CategoryImagesPunjabi');

            // Prepare data
            $data = [
                'image' => $image,
                'image_hindi' => $image_hindi,
                'image_punjabi' => $image_punjabi,
                'updated_at' => now(),
            ];

            // Update existing record
            if ($category_image) {
                $category_image->update($data);
                $success_message = 'Data updated successfully';
            } else {
                return redirect()->back()->with('emessage', 'Category image not found');
            }

            return redirect()->route('admin.category_images.view')->with('smessage', $success_message);

        } catch (\Exception $e) {
            Log::error('Category Images Update Error: ' . $e->getMessage());
            return redirect()->back()->with('emessage', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for updating category images.
     */
    public function updateCategoryImages($idd)
    {
        $id = base64_decode($idd);
        $category_images = CategoryImages::findOrFail($id);

        return view('admin.category_images.add_categoryimages', [
            'user_name' => Auth::guard('admin')->user()->name,
            'idd' => $idd,
            'category_images' => $category_images,
        ]);
    }

    /**
     * Update category images status (active/inactive).
     */
    public function updateCategoryImagesStatus($idd, $t)
    {
        try {
            $id = base64_decode($idd);
            $category_image = CategoryImages::findOrFail($id);

            if ($t === 'active') {
                $category_image->update(['is_active' => 1]);
            } elseif ($t === 'inactive') {
                $category_image->update(['is_active' => 0]);
            } else {
                return redirect()->back()->with('emessage', 'Invalid status');
            }

            return redirect()->route('admin.category_images.view')->with('smessage', 'Status updated successfully');

        } catch (\Exception $e) {
            Log::error('Category Images Status Update Error: ' . $e->getMessage());
            return view('admin.errors.error500', ['e' => 'Error occurred']);
        }
    }

    /**
     * Helper method to handle image uploads.
     */
    /**
 * Helper method to handle image uploads.
 */
private function uploadImage(Request $request, $field, $directory, $existing = null, $prefix = 'CategoryImages')
{
    if ($request->hasFile($field) && $request->file($field)->isValid()) {
        $file = $request->file($field);
        $fileName = $prefix . '_' . time() . '_' . $file->getClientOriginalName();

        // Define the destination path in the public directory
        $destinationPath = public_path($directory);

        // Create the directory if it doesn't exist
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Move the file to the destination path
        $file->move($destinationPath, $fileName);

        // Return the relative path to store in the database
        $newFilePath = $directory . '/' . $fileName;

        // Delete old image if it exists
        if ($existing && file_exists(public_path($existing))) {
            unlink(public_path($existing));
        }

        return $newFilePath;
    }

    return $existing;
}
}