<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\LiveStream;
use App\Models\GoogleForm;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GoLiveController extends Controller
{
    public function goLive(Request $request)
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

            // ✅ Check if the farmer is present in GoogleForm table with status 1
            $googleFormEntry = GoogleForm::where('farmer_id', $farmer->id)
                                         ->where('status', 1)
                                         ->first();

            if (!$googleFormEntry) {
                return response()->json([
                    'message' => 'User not approved yet!',
                    'status' => 403,
                    'data' => null
                ], 403);
            }

            // Generate unique live ID
            $liveId = 'LIVE-' . Str::uuid();

            // Store the live session in the DB
            LiveStream::create([
                'live_id' => $liveId,
                'user_id' => $farmer->id,
                'user_name' => $farmer->name,
                'status' => 1
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Live streaming started successfully.',
                'data' => [
                    'live_id' => $liveId,
                    'user_id' => $farmer->id,
                    'user_name' => $farmer->name,
                    'status' => 1
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


}
