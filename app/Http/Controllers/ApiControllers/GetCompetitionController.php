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

        $date = Carbon::now()->format('Y-m-d');

        $competition = CompetitionEntry::whereDate('competition_date', $date)->first();

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
            'cityIds' => array_map('intval', explode(',', $competition->city)),
            'cityNames' => $competition->city_names,
            'judge' => $competition->judge,
            'timeSlot' => $competition->time_slot,
            'slotTime' => $competition->slot_time,
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


}
