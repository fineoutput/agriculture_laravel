<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Disease;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DiseaseController extends Controller
{
    public function index()
    {   
        $diseases = Disease::all();
        return view('admin.diseases.index', compact('diseases'));
    }

    public function create()
    {
        return view('admin.diseases.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only('title', 'content');

        if ($request->hasFile('image1')) {
            $image = $request->file('image1');
            $fileName = time() . '_' . $image->getClientOriginalName();
    
            $destinationPath = public_path('disease_images');
    
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
    
            $image->move($destinationPath, $fileName);
    
            $data['image1'] = 'disease_images/' . $fileName;
        }

        $data['added_by'] = Auth::id();
        $data['is_active'] = true;

        Disease::create($data);
        return redirect()->route('disease.index')->with('success', 'Disease added successfully.');
    }

    public function edit($id)
    {
        try {
            $decodedId = base64_decode($id);
            $disease = Disease::findOrFail($decodedId);
            return view('admin.diseases.edit', compact('disease'));
        } catch (\Exception $e) {
            return redirect()->route('disease.index')->with('error', 'Invalid disease ID.');
        }
    }

    public function update(Request $request, $id)
{
    try {
        $decodedId = base64_decode($id);
        $disease = Disease::findOrFail($decodedId);

        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only('title', 'content');

        if ($request->hasFile('image1')) {
            if ($disease->image1) {
                Storage::disk('public')->delete($disease->image1);
            }
            $data['image1'] = $request->file('image1')->store('disease_images', 'public');
        }

        $disease->update($data);
        return redirect()->route('disease.index')->with('success', 'Disease updated successfully.');
    } catch (\Exception $e) {
        return redirect()->route('disease.index')->with('error', 'Failed to update disease.');
    }
}

    public function toggleStatus($id)
{
    $disease = Disease::findOrFail($id);
    $disease->is_active = !$disease->is_active;
    $disease->save();

    return redirect()->route('disease.index')->with('success', 'Disease status updated.');
}


    public function destroy($id)
        {
            $disease = Disease::findOrFail($id);
            Storage::disk('public')->delete($disease->image);
            $disease->delete();

            // Session::flash('success', 'Slider deleted successfully!');
            return redirect()->route('disease.index');
        }
}
