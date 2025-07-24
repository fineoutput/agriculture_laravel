<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\CompetitionEntry;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\GoogleForm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class GetCompetitionController extends Controller
{

public function getCompetition(Request $request)
{
    try {
        $token = $request->header('Authorization');

        $farmer = Farmer::where('auth', $token)
                        ->where('is_active', 1)
                        ->first();

        if (!$farmer) {
            return response()->json([
                'message' => 'Invalid token or inactive user!',
                'status' => 403,
                'data' => null
            ], 403);
        }

        $today = Carbon::now()->format('Y-m-d');

        // 🔁 Loop through all entries and match today's date inside time_slot
       $competition = CompetitionEntry::all()->first(function ($entry) use ($today) {
    $timeSlots = json_decode($entry->time_slot, true);

    if (is_array($timeSlots)) {
        foreach ($timeSlots as $slotEntries) {
            foreach ($slotEntries as $item) {
                if (!empty($item['date']) && $item['date'] === $today) {
                    return true;
                }
            }
        }
    }

    return false;
});


        if (!$competition) {
            return response()->json([
                'message' => 'No competition found for today.',
                'status' => 404,
                'data' => null
            ], 404);
        }

        $mapped = [
            'id' => $competition->id,
            'startDate' => $competition->start_date,
            'endDate' => $competition->end_date,
            'competitionDate' => $competition->competition_date,
            'state_name' => optional($competition->state)->state_name,
            'cityNames' => $competition->city_names,
            'judgeName' => $competition->judge_name,
            'timeSlot' => json_decode($competition->time_slot, true),
            'entryFees' => (int) $competition->entry_fees,
            'status' => (int) $competition->status,
            'createdAt' => $competition->created_at->toDateTimeString(),
            'updatedAt' => $competition->updated_at->toDateTimeString(),
        ];

        return response()->json([
            'message' => 'Competition fetched successfully.',
            'status' => 200,
            'data' => $mapped
        ], 200);

    } catch (\Exception $e) {
        Log::error('Fetching competition failed', ['error' => $e->getMessage()]);

        return response()->json([
            'message' => 'Server Error',
            'status' => 500,
            'data' => null,
            'error' => $e->getMessage()
        ], 500);
    }
}


// public function getCompetition(Request $request)
// {
//     try {
//         $token = $request->header('Authorization');
//         $slotRequested = $request->input('slot'); // Get slot from form-data

//         // Validate slot
//         $validSlots = ['Morning', 'Afternoon', 'Evening', 'Night'];
//         if (!$slotRequested || !in_array($slotRequested, $validSlots)) {
//             return response()->json([
//                 'message' => 'Invalid or missing slot. Allowed values: Morning, Afternoon, Evening, Night',
//                 'status' => 422,
//                 'data' => null
//             ], 422);
//         }

//         // Check farmer
//         $farmer = Farmer::where('auth', $token)
//                         ->where('is_active', 1)
//                         ->first();

//         if (!$farmer) {
//             return response()->json([
//                 'message' => 'Invalid token or inactive user!',
//                 'status' => 403,
//                 'data' => null
//             ], 403);
//         }

//         $today = Carbon::now()->format('Y-m-d');

//         // Search for a competition with the requested slot having today's date
//         $competition = CompetitionEntry::all()->first(function ($entry) use ($today, $slotRequested) {
//             $timeSlots = json_decode($entry->time_slot, true);

//             if (is_array($timeSlots) && isset($timeSlots[$slotRequested])) {
//                 $slotArray = $timeSlots[$slotRequested];
//                 foreach ($slotArray as $item) {
//                     if (!empty($item['date']) && $item['date'] === $today) {
//                         return true;
//                     }
//                 }
//             }

//             return false;
//         });

//         if (!$competition) {
//             return response()->json([
//                 'message' => 'No competition found for this slot today.',
//                 'status' => 404,
//                 'data' => null
//             ], 404);
//         }

//         $mapped = [
//             'id' => $competition->id,
//             'startDate' => $competition->start_date,
//             'endDate' => $competition->end_date,
//             'competitionDate' => $competition->competition_date,
//             'state_name' => optional($competition->state)->state_name,
//             'cityNames' => $competition->city_names,
//             'judgeName' => $competition->judge_name,
//             'timeSlot' => json_decode($competition->time_slot, true),
//             'entryFees' => (int) $competition->entry_fees,
//             'status' => (int) $competition->status,
//             'createdAt' => $competition->created_at->toDateTimeString(),
//             'updatedAt' => $competition->updated_at->toDateTimeString(),
//         ];

//         return response()->json([
//             'message' => 'Competition fetched successfully.',
//             'status' => 200,
//             'data' => $mapped
//         ], 200);

//     } catch (\Exception $e) {
//         Log::error('Fetching competition failed', ['error' => $e->getMessage()]);

//         return response()->json([
//             'message' => 'Server Error',
//             'status' => 500,
//             'data' => null,
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }


}
