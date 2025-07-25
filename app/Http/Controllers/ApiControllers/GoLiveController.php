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


public function goLive(Request $request){
    try {
        $token = $request->header('Authentication'); // Make sure header matches frontend

        $farmer = Farmer::where('auth', $token)
                        ->where('is_active', 1)
                        ->first();

        if (!$farmer) {
            return response()->json([
                'message' => 'Invalid token or inactive user!',
                'status' => 200,
                'data' => null
            ], 200);
        }

        // ✅ Get slot and competition_id from form-data
        $slotRequested = $request->input('slot');
        $competitionId = $request->input('competition_id');

        // ✅ Validate slot
        $validSlots = ['Morning', 'Afternoon', 'Evening', 'Night'];
        if (!$slotRequested || !in_array($slotRequested, $validSlots)) {
            return response()->json([
                'message' => 'Invalid or missing slot. Allowed values: Morning, Afternoon, Evening, Night',
                'status' => 200,
                'data' => null
            ], 200);
        }

        // ✅ Validate competition ID
        if (!$competitionId || !is_numeric($competitionId)) {
            return response()->json([
                'message' => 'Invalid or missing competition_id.',
                'status' => 201,
                'data' => null
            ], 201);
        }

        $today = Carbon::now()->format('Y-m-d');

        // ✅ Find the specified competition
        $competition = CompetitionEntry::find($competitionId);

        if (!$competition) {
            return response()->json([
                'message' => 'Competition not found.',
                'status' => 201,
                'data' => null
            ], 201);
        }

        // ✅ Validate that the competition includes the given slot and today's date
       $timeSlots = json_decode($competition->time_slot, true);

    $slotValid = false;
    if (is_array($timeSlots) && isset($timeSlots[$slotRequested])) {
    $slotData = $timeSlots[$slotRequested];
    if (!empty($slotData['date']) && $slotData['date'] === $today) {
        $slotValid = true;
    }
    }

    if (!$slotValid) {
    return response()->json([
        'message' => 'The selected competition does not have this slot available today.',
        'status' => 201,
        'data' => null
    ], 201);
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
            'status' => 201,
            'data' => null,
            'error' => $e->getMessage()
        ], 201);
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
                'status' => 201,
                'data' => null
            ], 201);
        }

        // Update the status based on type
        if ($type === 'start') {
            $liveStream->status = 2;
        } elseif ($type === 'end') {
            $liveStream->status = 3;
        } else {
            return response()->json([
                'message' => 'Invalid type. Allowed values: start, end',
                'status' => 201,
                'data' => null
            ], 201);
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
            'status' => 201,
            'data' => null,
            'error' => $e->getMessage()
        ], 201);
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

        $today = Carbon::now()->format('Y-m-d');

        $competition = CompetitionEntry::all()->first(function ($entry) use ($today) {
            $timeSlots = json_decode($entry->time_slot, true);

            if (is_array($timeSlots)) {
                foreach ($timeSlots as $slot => $slotData) {
                    if (isset($slotData['date']) && $slotData['date'] === $today) {
                        return true;
                    }

                    if (is_array($slotData) && isset($slotData[0]['date']) && $slotData[0]['date'] === $today) {
                        return true;
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

        $competitionId = $competition->id;

        $liveStreams = LiveStream::where('competition_id', $competitionId)
            ->where('status', 2)
            ->get();

        if ($liveStreams->isEmpty()) {
            return response()->json([
                'message' => 'No live stream found for today\'s competition with status 2.',
                'status' => 404,
                'data' => null
            ], 404);
        }

        $data = $liveStreams->map(function ($stream) {
            $farmer = Farmer::find($stream->user_id);

            return [
                'live_id'        => $stream->live_id,
                'competition_id' => $stream->competition_id,
                'status'         => $stream->status,
                'created_at'     => $stream->created_at->toDateTimeString(),
                'updated_at'     => $stream->updated_at->toDateTimeString(),
                'user' => [
                    'id'    => $farmer->id ?? null,
                    'name'  => $farmer->name ?? null,
                    'image' => $farmer->image ?? null,
                ]
            ];
        });

        return response()->json([
            'status' => 200,
            'message' => 'Live streams found.',
            'data' => $data,
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
