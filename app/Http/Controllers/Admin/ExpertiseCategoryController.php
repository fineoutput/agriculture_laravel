<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertiseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpertiseCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function viewExpertiseCategory()
    {
        $expertise_category_data = ExpertiseCategory::all();
        return view('admin.expertise_category.view_expertise_category', compact('expertise_category_data'));
    }

    public function addExpertiseCategory()
    {
        return view('admin.expertise_category.add_expertise_category');
    }

    public function updateExpertiseCategory($idd)
    {
        $id = base64_decode($idd);
        $expertise_category_data = ExpertiseCategory::findOrFail($id);
        return view('admin.expertise_category.update_expertise_category', compact('idd', 'expertise_category_data'));
    }

    public function addExpertiseCategoryData(Request $request, $t, $iw = "")
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'image_hindi' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'image_punjabi' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'image_marathi' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'image_gujrati' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $typ = base64_decode($t);
        $imagePath = null;
        $imageHindiPath = null;
        $imagePunjabiPath = null;
        $imageMarathiPath = null;
        $imageGujratiPath = null;

        $destinationPath = public_path('assets/uploads/expertise_category');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = 'expertise_category' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $imagePath = "assets/uploads/expertise_category/{$fileName}";
        }

        if ($request->hasFile('image_hindi')) {
            $file = $request->file('image_hindi');
            $fileName = 'expertise_category2' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $imageHindiPath = "assets/uploads/expertise_category/{$fileName}";
        }

        if ($request->hasFile('image_punjabi')) {
            $file = $request->file('image_punjabi');
            $fileName = 'expertise_category3' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $imagePunjabiPath = "assets/uploads/expertise_category/{$fileName}";
        }
        if ($request->hasFile('image_marathi')) {
            $file = $request->file('image_marathi');
            $fileName = 'expertise_category4' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $imageMarathiPath = "assets/uploads/expertise_category/{$fileName}";
        }
        if ($request->hasFile('image_gujrati')) {
            $file = $request->file('image_gujrati');
            $fileName = 'expertise_category5' . now()->format('YmdHis') . '.' . $file->getClientOriginalExtension();
            $file->move($destinationPath, $fileName);
            $imageGujratiPath = "assets/uploads/expertise_category/{$fileName}";
        }

        $data = [
            'name' => $request->name,
            'ip' => $request->ip(),
            'added_by' => auth('admin')->id(),
            'is_active' => 1,
            'date' => now()->setTimezone('Asia/Kolkata'),
        ];

        if ($typ == 1) {
            // Add new expertise category
            if ($imagePath) $data['image'] = $imagePath;
            if ($imageHindiPath) $data['image_hindi'] = $imageHindiPath;
            if ($imagePunjabiPath) $data['image_punjabi'] = $imagePunjabiPath;
            if ($imageMarathiPath) $data['image_marathi'] = $imageMarathiPath;
            if ($imageGujratiPath) $data['image_gujrati'] = $imageGujratiPath;
            $last_id = ExpertiseCategory::create($data);
        } elseif ($typ == 2) {
            // Update existing expertise category
            $idw = base64_decode($iw);
            $expertise = ExpertiseCategory::findOrFail($idw);
            $data['image'] = $imagePath ?? $expertise->image;
            $data['image_hindi'] = $imageHindiPath ?? $expertise->image_hindi;
            $data['image_punjabi'] = $imagePunjabiPath ?? $expertise->image_punjabi;
            $data['image_marathi'] = $imageMarathiPath ?? $expertise->image_marathi;
            $data['image_gujrati'] = $imageGujratiPath ?? $expertise->image_gujrati;
            unset($data['ip'], $data['added_by'], $data['is_active'], $data['date']); // Don't update these on edit
            $last_id = $expertise->update($data);
        } else {
            return redirect()->back()->with('emessage', 'Invalid operation type');
        }

        if ($last_id) {
            return redirect()->route('admin.expertise_category.view')->with('smessage', 'Data inserted successfully');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function updateExpertiseCategoryStatus($idd, $t)
    {
        $id = base64_decode($idd);
        $is_active = $t === 'active' ? 1 : ($t === 'inactive' ? 0 : null);

        if (is_null($is_active)) {
            return redirect()->route('admin.expertise_category.view')->with('emessage', 'Invalid status type');
        }

        $updated = ExpertiseCategory::where('id', $id)->update(['is_active' => $is_active]);

        if ($updated) {
            return redirect()->route('admin.expertise_category.view');
        }
        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }

    public function deleteExpertiseCategory(Request $request, $idd)
    {
        $id = base64_decode($idd);

        // if (auth('admin')->user()->position !== 'Super Admin') {
        //     return redirect()->back()->with('emessage', 'Sorry, you are not a Super Admin and don’t have permission to delete anything');
        // }

        $expertise = ExpertiseCategory::find($id);
        if ($expertise) {
            // Optionally delete images here if needed
            $expertise->delete();
            return redirect()->back();
        }

        return redirect()->back()->with('emessage', 'Sorry, an error occurred');
    }
}