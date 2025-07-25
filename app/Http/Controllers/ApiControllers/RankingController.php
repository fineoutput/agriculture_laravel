<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\MilkRanking;
use App\Models\Farmer;

class RankingController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Milk Entry Request Received', [
            'weight' => $request->input('weight'),
            'authentication_header' => $request->header('Authentication'),
        ]);

        // Validate request
        $validator = Validator::make($request->all(), [
            'weight' => 'required',
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
}
