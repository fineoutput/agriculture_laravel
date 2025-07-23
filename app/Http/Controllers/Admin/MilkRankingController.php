<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MilkRanking;
use App\Models\GoogleForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MilkRankingController extends Controller
{
    public function index()
    {
        $rankings = MilkRanking::with('farmer')->orderByDesc('weight')->get();
        return view('admin.milk_ranking.index', compact('rankings'));
    }

    public function create()
    {
        $farmers = GoogleForm::select('farmer_id', 'farmer_name')->get();
        return view('admin.milk_ranking.create', compact('farmers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'farmer_id' => 'required|exists:google_form,farmer_id',
            'weight' => 'required|numeric|min:0.01',
            'image' => 'required|image',
            'score' => 'nullable|integer',
        ]);

        $path = $request->file('image')->store('ranking_images', 'public');

        MilkRanking::create([
            'farmer_id' => $request->farmer_id,
            'weight' => $request->weight,
            'score' => $request->score,
            'image' => $path,
        ]);

        return redirect()->route('admin.milk-ranking.index')->with('success', 'Ranking added successfully');
    }

    public function edit($id)
    {
        $ranking = MilkRanking::findOrFail($id);
        $farmers = GoogleForm::select('farmer_id', 'farmer_name')->get();
        return view('admin.milk_ranking.edit', compact('ranking', 'farmers'));
    }

    public function update(Request $request, $id)
    {
        $ranking = MilkRanking::findOrFail($id);

        $request->validate([
            'farmer_id' => 'required|exists:google_form,farmer_id',
            'weight' => 'required|numeric|min:0.01',
            'image' => 'nullable|image',
            'score' => 'nullable|integer',
        ]);

        $data = $request->only('farmer_id', 'weight', 'score');

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($ranking->image);
            $data['image'] = $request->file('image')->store('ranking_images', 'public');
        }

        $ranking->update($data);

        return redirect()->route('admin.milk-ranking.index')->with('success', 'Ranking updated successfully');
    }

    public function destroy($id)
    {
        $ranking = MilkRanking::findOrFail($id);
        Storage::disk('public')->delete($ranking->image);
        $ranking->delete();

        return redirect()->back()->with('success', 'Ranking deleted');
    }
}
