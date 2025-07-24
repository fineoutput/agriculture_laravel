<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Farmer;
use App\Models\CompResult;

class CompetitionResultController extends Controller
{
  public function storeCompResult(Request $request)
{
    try {
        $token = $request->header('Authorization');

        // Authenticate the farmer
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

        // Validate request
        $validated = $request->validate([
            'comp_id' => 'required',
            'weight' => 'required',
            'slot' => 'required',
            'image' => 'required'
        ]);

        // Handle image upload
        $image = $request->file('image');
        $filename = time() . '_' . $image->getClientOriginalName();
        $destinationPath = public_path('milk_images');
        $image->move($destinationPath, $filename);

        $imageUrl = url('milk_images/' . $filename);

        // Save result
        $compResult = CompResult::create([
            'comp_id' => $validated['comp_id'],
            'farmer_id' => $farmer->id,
            'farmer_name' => $farmer->name,
            'img' => $imageUrl,
            'weight' => $validated['weight'],
            'slot' => $validated['slot'],
        ]);

        return response()->json([
            'message' => 'Competition result saved successfully!',
            'status' => 201,
            'data' => $compResult
        ], 201);

    } catch (\Exception $e) {
        Log::error('Creating competition result failed', ['error' => $e->getMessage()]);

        return response()->json([
            'message' => 'Server Error',
            'status' => 500,
            'data' => null,
            'error' => $e->getMessage()
        ], 500);
    }
}
}
