<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\MilkRanking;
use App\Models\Farmer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CompetitionEntry;

class RankingController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Milk Entry Request Received', [
            'weight' => $request->input('weight'),
            'competition_id' => $request->input('competition_id'),
            'authentication_header' => $request->header('Authentication'),
        ]);

        // Validate request
        $validator = Validator::make($request->all(), [
            'weight' => 'required',
            'competition_id' => 'required',
            'image' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed: ' . $validator->errors()->first());
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 422,
            ], 422);
        }

        try {
            // Extract token from header
            $token = $request->header('Authentication');

            // Check farmer by token
            $farmer = Farmer::where('auth', $token)
                            ->where('is_active', 1)
                            ->first();

            if (!$farmer) {
                Log::warning('Invalid or inactive user', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 403,
                ], 403);
            }

            // Store image
            $image = $request->file('image');
        $filename = time() . '_' . $image->getClientOriginalName();
        $destinationPath = public_path('milk_images');

        $image->move($destinationPath, $filename);

        // Generate public URL path
            $imageUrl = url('milk_images/' . $filename);

            // Save milk entry
            $entry = MilkRanking::create([
                'farmer_id' => $farmer->id,
                'weight' => $request->weight,
                'competition_id' => $request->competition_id,
                'image' => $imageUrl,
            ]);

            return response()->json([
                'message' => 'Milk entry created successfully',
                'data' => $entry,
                'status' => 200,
            ], 201);
            } catch (\Exception $e) {
                Log::error('Error in storing milk entry', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Server Error',
                    'status' => 500,
                    'error' => $e->getMessage(),
                ], 500);
            }
    }

public function leaderboard(Request $request)
{
    try {
        $today = Carbon::now()->format('Y-m-d');

        $competition = CompetitionEntry::all()->first(function ($entry) use ($today) {
            $slots = json_decode($entry->time_slot, true);
            if (is_array($slots)) {
                foreach ($slots as $slot => $data) {
                    if (isset($data['date']) && $data['date'] === $today) {
                        return true;
                    }
                    if (is_array($data) && isset($data[0]['date']) && $data[0]['date'] === $today) {
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
                'data' => null,
            ], 404);
        }

        $competitionId = $competition->id;

        // ✅ Decode judge names
        $judgeNames = is_array(json_decode($competition->judge_name, true))
            ? json_decode($competition->judge_name, true)
            : [$competition->judge_name];

        // ✅ Get leaderboard sorted by total_weight
        $leaderboard = MilkRanking::select('farmer_id', DB::raw('SUM(weight) as total_weight'))
            ->where('competition_id', $competitionId)
            ->groupBy('farmer_id')
            ->orderByDesc('total_weight')
            ->with('farmer:id,name,image,village')
            ->get();

        // ✅ Build leaderboard with judges in each entry
        $data = $leaderboard->map(function ($entry, $index) use ($judgeNames) {
            return [
                'rank' => $index + 1,
                'farmer_id' => $entry->farmer_id,
                'name' => $entry->farmer->name ?? null,
                'image' => $entry->farmer->image ?? null,
                'village' => $entry->farmer->village ?? null,
                'total_weight' => (float) $entry->total_weight,
                'judges' => $judgeNames,
            ];
        });

        return response()->json([
            'message' => 'Leaderboard fetched successfully',
            'status' => 200,
            'data' => $data
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error fetching leaderboard', ['error' => $e->getMessage()]);
        return response()->json([
            'message' => 'Server Error',
            'status' => 500,
            'error' => $e->getMessage()
        ], 500);
    }
}




}
