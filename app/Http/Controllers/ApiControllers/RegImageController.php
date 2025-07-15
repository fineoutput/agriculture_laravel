<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegImage;

class RegImageController extends Controller
{
    public function getLatestEnabledImage()
    {
        $image = RegImage::where('is_enabled', true)->latest()->first();

        if (!$image) {
            return response()->json([
                'status' => false,
                'message' => 'No active image found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'image_url' => asset($image->image_path),
        ]);
    }
}