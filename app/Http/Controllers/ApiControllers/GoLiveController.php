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
            // Get token from header
            $token = $request->header('Authentication');

            // Check if farmer exists and is active
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
}
