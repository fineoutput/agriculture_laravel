<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompetitionEntry;
use App\Models\MilkRanking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Models\State;
use App\Models\City;
use Carbon\Carbon;
use App\Models\Doctor;

class CompetitionController extends Controller
{
    public function index()
    {
        

        $entries = CompetitionEntry::select('tbl_competition_entry.*', 'all_states.state_name')
            ->leftJoin('all_states', 'tbl_competition_entry.state_id', '=', 'all_states.id')
            ->orderBy('tbl_competition_entry.id', 'DESC')
            ->get();

        return view('admin.competition.index', [
            'form_data' => $entries,
            'user_name' => session('user_name')
        ]);
    }

    public function getCitiesByState(Request $request)
    {
        $cities = City::where('state_id', $request->state_id)->get();

        return response()->json($cities);
    }

    public function create()
    {
        // if (!Session::has('admin_data')) {
        //     return redirect()->route('admin.login');
        // }

        return view('admin.competition.create', [
            'states' => State::all(),
            'user_name' => session('user_name'),
            'doctors' => Doctor::all(['id', 'name']),
            'city' => City::all(['id', 'city_name']),
        ]);
    }

public function filtercity($stateid)
{
 
    $cities = City::where('state_id', $stateid)->get(['id', 'city_name']);
    return response()->json(['cities' => $cities]);
    }

public function store(Request $request)
{
    // return $request;
    $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'competition_date' => 'required|date',
        'state' => 'required|integer',
        // 'city' => 'required|string',
        // 'time_slot' => 'required|array',
        // 'time_slot.*' => 'in:Morning,Afternoon,Evening,Night',
        // 'slot_time' => 'required|array',
        // 'slot_time.*' => 'required|date_format:H:i',
        'entry_fees' => 'required|numeric',
        // 'judge' => 'required|exists:tbl_doctor,id',
    ]);

    // $slots = $request->time_slot;
    // $times = $request->slot_time;
    // $slotData = [];

    // foreach ($slots as $index => $slotName) {
    //     $slotData[$slotName] = $times[$index];
    // }

    CompetitionEntry::create([
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'competition_date' => $request->competition_date,
        'state_id' => $request->state,
        'city' => !empty($request->city) ? implode(',', $request->city) : null,
        'entry_fees' => $request->entry_fees,
        'status' => 1,
        'judge' =>!empty($request->judge) ? implode(',', $request->judge) : null,
        // 'time_slot' => json_encode($slotData),
        // 'created_at' => now(),
        // 'updated_at' => now(),
    ]);

    Session::flash('message', 'Competition entry added successfully.');
    return redirect()->route('admin.competition.index');
}

    public function edit($id)
    {
        // if (!Session::has('admin_data')) {
        //     return redirect()->route('admin.login');
        // }

        $competition = CompetitionEntry::findOrFail(base64_decode($id));

        return view('admin.competition.edit', [
            'competition' => $competition,
            'states' => State::all(),
            'cities' => City::where('state_id', $competition->state)->get(),
            'user_name' => session('user_name'),
            'doctors' => Doctor::all(['id', 'name']),
        ]);
    }

   public function update(Request $request, $id)
{
    $id = base64_decode($id);
    // return $request;
    $request->validate([
        // 'start_date' => 'required|date',
        // 'end_date' => 'required|date',
        // 'competition_date' => 'required|date',
        // 'state' => 'required|integer',
        // 'city' => 'required|string',
        // 'time_slot' => 'required|array',
        // 'time_slot.*' => 'in:Morning,Afternoon,Evening,Night',
        // 'slot_time' => 'required|array',
        // 'slot_time.*' => 'required|date_format:H:i',
        // 'entry_fees' => 'required|numeric',
        // 'judge' => 'required|exists:tbl_doctor,id',
    ]);

    // $slots = $request->time_slot;
    // $times = $request->slot_time;
    // $slotData = [];

    // foreach ($slots as $index => $slotName) {
    //     $slotData[$slotName] = $times[$index];
    // }
    $cmp = CompetitionEntry::find($id);
    $cmp->update([
    'entry_fees' => $request->entry_fees,
    'start_date' => $request->start_date,
    'end_date' => $request->end_date,
    'competition_date' => $request->competition_date,
    'state_id' => $request->state,
    'city' => !empty($request->city) ? implode(',', $request->city) : null,
    'judge' =>!empty($request->judge) ? implode(',', $request->judge) : null,
    // 'time_slot' => json_encode($slotData),
]);



    Session::flash('message', 'Competition updated successfully.');
    return redirect()->route('admin.competition.index');
}


    public function destroy($id)
    {
        if (!Session::has('admin_data')) {
            return redirect()->route('admin.login');
        }

        $entry = CompetitionEntry::find(base64_decode($id));

        if ($entry) {
            $entry->delete();
            Session::flash('message', 'Competition entry deleted successfully.');
        } else {
            Session::flash('emessage', 'Competition entry not found.');
        }

        return redirect()->route('admin.competition.index');
    }


    public function showRankings($competitionId)
    {
        // Get the competition details
        $competition = CompetitionEntry::findOrFail($competitionId);
        
        // Get rankings for this competition, ordered by weight (highest first)
        $rankings = MilkRanking::with('farmer')
            ->where('competition_id', $competitionId)
            ->orderByDesc('weight')
            ->get();

        return view('admin.competition.rankings', compact('rankings', 'competition'));
    }

    public function editRanking($id)
    {
        $ranking = MilkRanking::with('farmer')->findOrFail($id);
        $competition = CompetitionEntry::findOrFail($ranking->competition_id);
        
        return view('admin.competition.edit_ranking', compact('ranking', 'competition'));
    }

    public function updateRanking(Request $request, $id)
    {
        try {
            $ranking = MilkRanking::findOrFail($id);
            
            $request->validate([
                'weight' => 'required|numeric|min:0.01',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'position' => 'nullable|integer|in:1,2,3',
            ]);

            $data = [
                'weight' => $request->weight,
                'position' => $request->position ? (int)$request->position : null,
            ];

            // Check for duplicate position if assigning a position
            if ($data['position']) {
                $existingRanking = MilkRanking::where('competition_id', $ranking->competition_id)
                    ->where('id', '!=', $ranking->id)
                    ->where('position', $data['position'])
                    ->first();

                if ($existingRanking) {
                    Session::flash('emessage', 'Position ' . $data['position'] . ' is already assigned to another entry in this competition.');
                    return redirect()->back()->withInput();
                }
            }

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($ranking->image) {
                    // Extract filename from URL
                    $oldImagePath = str_replace(url('/'), '', $ranking->image);
                    if (file_exists(public_path($oldImagePath))) {
                        unlink(public_path($oldImagePath));
                    }
                }
                
                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $destinationPath = public_path('milk_images');
                $image->move($destinationPath, $filename);
                $data['image'] = url('milk_images/' . $filename);
            }

            $ranking->update($data);

            Session::flash('message', 'Ranking updated successfully.');
            return redirect()->route('admin.competition.rankings', $ranking->competition_id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Session::flash('emessage', 'An error occurred while updating the ranking.');
            return redirect()->back()->withInput();
        }
    }

    public function deleteRanking($id)
    {
        $ranking = MilkRanking::findOrFail($id);
        $competitionId = $ranking->competition_id;
        
        // Delete image if exists
        if ($ranking->image) {
            // Extract filename from URL
            $imagePath = str_replace(url('/'), '', $ranking->image);
            if (file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }
        }
        
        $ranking->delete();

        Session::flash('message', 'Ranking deleted successfully.');
        return redirect()->route('admin.competition.rankings', $competitionId);
    }

    public function updatePosition(Request $request)
    {
        try {
            $request->validate([
                'ranking_id' => 'required|exists:tbl_ranking,id',
                'position' => 'nullable|integer|in:1,2,3',
            ]);

            $ranking = MilkRanking::findOrFail($request->ranking_id);
            $newPosition = $request->position ? (int)$request->position : null;

            // If assigning a position, check if it's already taken by another entry in the same competition
            if ($newPosition) {
                $existingRanking = MilkRanking::where('competition_id', $ranking->competition_id)
                    ->where('id', '!=', $ranking->id)
                    ->where('position', $newPosition)
                    ->first();

                if ($existingRanking) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Position ' . $newPosition . ' is already assigned to another entry.'
                    ], 400);
                }
            }

            $ranking->update(['position' => $newPosition]);

            return response()->json([
                'success' => true,
                'message' => 'Position updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the position.'
            ], 500);
        }
    }
}
