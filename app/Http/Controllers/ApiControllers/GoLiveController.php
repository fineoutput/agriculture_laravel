<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\LiveStream;
use App\Models\GoogleForm;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\CompetitionEntry;

class GoLiveController extends Controller
{
    // public function goLive(Request $request)
    // {
    //     try {
    //         $token = $request->header('Authentication');

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

    //         // ✅ Check if the farmer is present in GoogleForm table with status 1
    //         $googleFormEntry = GoogleForm::where('farmer_id', $farmer->id)
    //                                      ->where('status', 1)
    //                                      ->first();

    //         if (!$googleFormEntry) {
    //             return response()->json([
    //                 'message' => 'User not approved yet!',
    //                 'status' => 403,
    //                 'data' => null
    //             ], 403);
    //         }

    //         // Generate unique live ID
    //         $liveId = 'LIVE-' . Str::uuid();

    //         // Store the live session in the DB
    //         LiveStream::create([
    //             'live_id' => $liveId,
    //             'user_id' => $farmer->id,
    //             'user_name' => $farmer->name,
    //             'status' => 1
    //         ]);

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Live streaming started successfully.',
    //             'data' => [
    //                 'live_id' => $liveId,
    //                 'user_id' => $farmer->id,
    //                 'user_name' => $farmer->name,
    //                 'status' => 1
    //             ]
    //         ], 200);

    //     } catch (\Exception $e) {
    //         Log::error('Live streaming failed', ['error' => $e->getMessage()]);
    //         return response()->json([
    //             'message' => 'Server Error',
    //             'status' => 500,
    //             'data' => null,
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


public function goLive(Request $request)
{
    try {
        $token = $request->header('Authentication'); // Make sure header matches frontend

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

        // ✅ Get slot and competition_id from form-data
        $slotRequested = $request->input('slot');
        $competitionId = $request->input('competition_id');

        // ✅ Validate slot
        $validSlots = ['Morning', 'Afternoon', 'Evening', 'Night'];
        if (!$slotRequested || !in_array($slotRequested, $validSlots)) {
            return response()->json([
                'message' => 'Invalid or missing slot. Allowed values: Morning, Afternoon, Evening, Night',
                'status' => 422,
                'data' => null
            ], 422);
        }

        // ✅ Validate competition ID
        if (!$competitionId || !is_numeric($competitionId)) {
            return response()->json([
                'message' => 'Invalid or missing competition_id.',
                'status' => 422,
                'data' => null
            ], 422);
        }

        $today = Carbon::now()->format('Y-m-d');

        // ✅ Find the specified competition
        $competition = CompetitionEntry::find($competitionId);

        if (!$competition) {
            return response()->json([
                'message' => 'Competition not found.',
                'status' => 404,
                'data' => null
            ], 404);
        }

        // ✅ Validate that the competition includes the given slot and today's date
        $timeSlots = json_decode($competition->time_slot, true);

        $slotValid = false;
        if (is_array($timeSlots) && isset($timeSlots[$slotRequested])) {
            foreach ($timeSlots[$slotRequested] as $slotData) {
                if (!empty($slotData['date']) && $slotData['date'] === $today) {
                    $slotValid = true;
                    break;
                }
            }
        }

        if (!$slotValid) {
            return response()->json([
                'message' => 'The selected competition does not have this slot available today.',
                'status' => 422,
                'data' => null
            ], 422);
        }

        // ✅ Generate unique live ID
        $liveId = 'LIVE-' . Str::uuid();

        // ✅ Store the live session in the DB
        LiveStream::create([
            'live_id' => $liveId,
            'user_id' => $farmer->id,
            'user_name' => $farmer->name,
            'competition_id' => $competition->id, // Save competition ID
            'slot' => $slotRequested,
            'status' => 1
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Live streaming started successfully.',
            'data' => [
                'live_id' => $liveId,
                'user_id' => $farmer->id,
                'user_name' => $farmer->name,
                'slot' => $slotRequested,
                'competition_id' => $competition->id,
                'competition_date' => $competition->competition_date
            ]
        ], 200);

    } catch (\Exception $e) {
        Log::error('Live streaming failed', ['error' => $e->getMessage()]);
        return response()->json([
            'message' => 'Server Error',
            'status' => 500,
            'data' => null,
            'error' => $e->getMessage()
        ], 500);
    }
}


public function updateLiveStatus(Request $request)
{
    try {
        $liveId = $request->input('live_id'); // Get live_id from form data
        $type = $request->input('type');      // Get 'type' from form data: 'start' or 'end'

        // Find the live stream by live_id
        $liveStream = LiveStream::where('live_id', $liveId)->first();

        if (!$liveStream) {
            return response()->json([
                'message' => 'Live stream not found!',
                'status' => 404,
                'data' => null
            ], 404);
        }

        // Update the status based on type
        if ($type === 'start') {
            $liveStream->status = 2;
        } elseif ($type === 'end') {
            $liveStream->status = 3;
        } else {
            return response()->json([
                'message' => 'Invalid type. Allowed values: start, end',
                'status' => 400,
                'data' => null
            ], 400);
        }

        $liveStream->save();

        return response()->json([
            'message' => 'Live stream status updated successfully.',
            'status' => 200,
            'data' => $liveStream
        ], 200);

    } catch (\Exception $e) {
        Log::error('Live stream status update failed', ['error' => $e->getMessage()]);
        return response()->json([
            'message' => 'Server Error',
            'status' => 500,
            'data' => null,
            'error' => $e->getMessage()
        ], 500);
    }
}

public function liveUser(Request $request)
{
    try {
        $token = $request->header('Authentication');

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

        // ✅ Get competition_id from request
        $competitionId = $request->input('competition_id');

        if (!$competitionId || !is_numeric($competitionId)) {
            return response()->json([
                'message' => 'Invalid or missing competition_id.',
                'status' => 422,
                'data' => null
            ], 422);
        }

        // ✅ Get the user's live stream with status = 2 and matching competition
        $liveStream = LiveStream::where('user_id', $farmer->id)
                                ->where('competition_id', $competitionId)
                                ->where('status', 2)
                                ->first();

        if (!$liveStream) {
            return response()->json([
                'message' => 'No live stream found for this user in the given competition with status 2.',
                'status' => 404,
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Live stream found.',
            'data' => [
                'live_id' => $liveStream->live_id,
                'user_id' => $liveStream->user_id,
                'user_name' => $liveStream->user_name,
                'competition_id' => $liveStream->competition_id,
                'slot' => $liveStream->slot,
                'status' => $liveStream->status
            ]
        ], 200);

    } catch (\Exception $e) {
        Log::error('Live stream fetch failed', ['error' => $e->getMessage()]);

        return response()->json([
            'message' => 'Server Error',
            'status' => 500,
            'data' => null,
            'error' => $e->getMessage()
        ], 500);
    }
}

}
