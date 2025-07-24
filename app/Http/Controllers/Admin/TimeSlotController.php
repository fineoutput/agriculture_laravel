<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompetitionEntry;
use Illuminate\Support\Facades\Session;
use App\Models\State;
use App\Models\City;
use Carbon\Carbon;

class TimeSlotController extends Controller
{
       public function update(Request $request, $id)
{
    $id = base64_decode($id);
    // return $request;
    $request->validate([
        // 'time_slot' => 'required|array',
        // 'time_slot.*' => 'in:Morning,Afternoon,Evening,Night'
    ]);

  $date = $request->slot_date;
$slots = $request->time_slot;
$times = $request->slot_time;

$slotData = [];
   
foreach ($slots as $index => $slotName) {
    $slotData[$slotName] = [
        'time' => $times[$index],
        'date' => $date // assuming same date for all slots
    ];
}

$cmp = CompetitionEntry::find($id);

$cmp->update([
    'time_slot' => json_encode($slotData),
]);


    Session::flash('message', 'Competition updated successfully.');
    return redirect()->route('admin.competition.index');
}

 public function edit($id)
    {
        // if (!Session::has('admin_data')) {
        //     return redirect()->route('admin.login');
        // }

        $competition = CompetitionEntry::findOrFail(base64_decode($id));

        return view('admin.competition.time_slot', [
            'competition' => $competition,
            'states' => State::all(),
            'cities' => City::where('state_id', $competition->state)->get(),
            'user_name' => session('user_name'),
        ]);
    }

}
