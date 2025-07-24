<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompetitionEntry;
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
            ->leftJoin('all_states', 'tbl_competition_entry.state', '=', 'all_states.id')
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
}
