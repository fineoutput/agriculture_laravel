<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class popupImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display and manage pop-up images.
     */
    public function index()
    {
        $popup_images = PopupImages::orderBy('id', 'DESC')->get();
        $user_name = Auth::guard('admin')->user()->name;

        return view('admin.popup_images.index', compact('popup_images', 'user_name'));
    }

    /**
     * Store a new pop-up image.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . $validator->errors()->first());
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        try {
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalExtension();
                $destinationPath = public_path('uploads/popup_images');
                $image->move($destinationPath, $imageName);
                $imagePath = 'uploads/popup_images/' . $imageName;

                Log::info('Image saved to: ' . $imagePath);

                PopupImages::create([
                    'image' => $imagePath,
                ]);

                return redirect()->route('admin.popup_images.index')
                    ->with('smessage', 'Image uploaded successfully');
            }

            return redirect()->back()->with('emessage', 'No image file provided.');
        } catch (\Exception $e) {
            Log::error('Pop-up image upload error: ' . $e->getMessage());
            return redirect()->back()->with('emessage', 'An error occurred. Please try again.');
        }
    }
}