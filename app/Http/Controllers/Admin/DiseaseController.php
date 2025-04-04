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
            $path = $request->file('image1')->store('uploads/diseases', 'public');
            $data['image1'] = 'storage/' . $path;
        }

        $data['added_by'] = Auth::id();
        $data['is_active'] = true;

        Disease::create($data);
        return redirect()->route('admin.diseases.index')->with('success', 'Disease added successfully.');
    }

    public function edit(Disease $disease)
    {
        return view('admin.diseases.edit', compact('disease'));
    }

    public function update(Request $request, Disease $disease)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only('title', 'content');

        if ($request->hasFile('image1')) {
            // Delete old image if exists
            if ($disease->image1) {
                Storage::disk('public')->delete(str_replace('storage/', '', $disease->image1));
            }
            $path = $request->file('image1')->store('uploads/diseases', 'public');
            $data['image1'] = 'storage/' . $path;
        }

        $disease->update($data);
        return redirect()->route('admin.diseases.index')->with('success', 'Disease updated successfully.');
    }

    public function toggleStatus(Disease $disease)
    {
        $disease->is_active = !$disease->is_active;
        $disease->save();
        return redirect()->route('admin.diseases.index')->with('success', 'Disease status updated.');
    }

    public function destroy(Disease $disease)
    {
        if (Auth::user()->position === 'Super Admin') {
            if ($disease->image1) {
                Storage::disk('public')->delete(str_replace('storage/', '', $disease->image1));
            }
            $disease->delete();
            return redirect()->route('admin.diseases.index')->with('success', 'Disease deleted.');
        }

        return redirect()->back()->with('error', 'You do not have permission to delete.');
    }
}
