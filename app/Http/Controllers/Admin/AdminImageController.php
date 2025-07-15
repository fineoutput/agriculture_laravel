<?php

// app/Http/Controllers/Admin/AdminImageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegImage;
use Illuminate\Support\Facades\File;

class AdminImageController extends Controller
{
    public function index()
    {
        $images = RegImage::latest()->get();
        return view('admin.reg_image.index', compact('images'));
    }

    public function create()
    {
        return view('admin.reg_image.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $image = $request->file('image');
        $filename = time() . '_' . $image->getClientOriginalName();
        $path = 'uploads/images/' . $filename;

        $image->move(public_path('uploads/images'), $filename);

        RegImage::create([
            'image_path' => $path,
            'is_enabled' => true,
        ]);

        return redirect()->route('admin.reg_image.index')->with('success', 'Image uploaded successfully');
    }

    public function destroy($id)
    {
        $image = RegImage::findOrFail($id);

        // Delete file from disk
        $filePath = public_path($image->image_path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $image->delete();

        return back()->with('success', 'Image deleted successfully');
    }

    public function toggleStatus($id)
    {
        $image = RegImage::findOrFail($id);
        $image->is_enabled = !$image->is_enabled;
        $image->save();

        return back()->with('success', 'Image status updated successfully');
    }
}
