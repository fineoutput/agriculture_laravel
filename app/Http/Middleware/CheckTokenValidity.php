<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Farmer;
use App\Models\Doctor;
use App\Models\Vendor;

class CheckTokenValidity
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $modelMap = [
                'farmer' => Farmer::class,
                'doctor' => Doctor::class,
                'vendor' => Vendor::class,
            ];

            if (!isset($modelMap[$user->type])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid user type',
                ], 400);
            }

            $model = $modelMap[$user->type];
            $storedUser = $model::where('id', $user->id)->first();

            if (!$storedUser || !$storedUser->token || $storedUser->token !== $request->bearerToken()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token is invalid or has been revoked',
                ], 401);
            }

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid token: ' . $e->getMessage(),
            ], 401);
        }
    }
}