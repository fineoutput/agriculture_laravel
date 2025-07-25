<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\MilkRanking;
use App\Models\Farmer;
use App\Models\Doctor;
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
    
            // ✅ Get all competitions running today
            $competitionsToday = CompetitionEntry::all()->filter(function ($entry) use ($today) {
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
    
            if ($competitionsToday->isEmpty()) {
                return response()->json([
                    'message' => 'No competition found for today.',
                    'status' => 404,
                    'data' => null,
                ], 404);
            }
    
            // ✅ Collect all competition IDs and all judge IDs
            $competitionIds = [];
            $judgeIds = [];
    
            foreach ($competitionsToday as $comp) {
                $competitionIds[] = $comp->id;
    
                $decoded = json_decode($comp->judge, true);
                if (is_array($decoded)) {
                    $judgeIds = array_merge($judgeIds, $decoded);
                } else {
                    $judgeIds[] = $comp->judge;
                }
            }
    
            $judgeIds = array_unique(array_filter($judgeIds));
    
            // ✅ Fetch judge details
            $judges = Doctor::whereIn('id', $judgeIds)
                ->get(['id', 'name', 'image', 'district'])
                ->map(function ($judge) {
                    return [
                        'id' => $judge->id,
                        'name' => $judge->name,
                        'image' => $judge->image,
                        'district' => $judge->district,
                    ];
                });
    
            // ✅ Get individual entries sorted by weight
            $entries = MilkRanking::whereIn('competition_id', $competitionIds)
                ->orderByDesc('weight')
                ->with('farmer:id,name,image,village')
                ->get();
    
            $data = $entries->map(function ($entry, $index) {
                return [
                    'rank' => $index + 1,
                    'farmer_id' => $entry->farmer_id,
                    'name' => $entry->farmer->name ?? null,
                    'image' => $entry->farmer->image ?? null,
                    'village' => $entry->farmer->village ?? null,
                    'weight' => (float) $entry->weight,
                    'entry_image' => $entry->image ?? null,
                    'submitted_at' => $entry->created_at->toDateTimeString(),
                ];
            });
    
            return response()->json([
                'message' => 'Leaderboard fetched successfully',
                'status' => 200,
                'judges' => $judges,
                'data' => $data,
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
