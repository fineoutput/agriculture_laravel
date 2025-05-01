<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ServiceRecordTxn;
use App\Models\ServiceRecord;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\View;
class FeedController extends Controller
{
    public function calculateWeight(Request $request)
    {
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('CalculateWeight auth attempt', [
                'user_id' => $user ? $user->id : null,
                'is_active' => $user ? ($user->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
            ]);

            if (!$user || !$user->is_active) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Check if request has data
            if (!$request->all()) {
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 400);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:Inches,Centimeters',
                'grith' => 'required|numeric',
                'length' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Calculate weight
            $grith = $request->grith;
            $length = $request->length;
            $type = $request->type;

            if ($type === 'Inches') {
                $weight = ($grith * $grith * $length) / 660 + 0.5;
            } else {
                $weight = ($grith * $grith * $length) / 10797 + 0.5;
            }

            // Update service record
            $serviceRecord = ServiceRecord::first();
            if ($serviceRecord) {
                $serviceRecord->update(['weight_calculator' => $serviceRecord->weight_calculator + 1]);
                Log::info('Service record updated', [
                    'service_record_id' => $serviceRecord->id,
                    'weight_calculator' => $serviceRecord->weight_calculator,
                ]);
            } else {
                Log::warning('No service record found in tbl_service_records');
            }

            // Log transaction
            $txnData = [
                'farmer_id' => $user->id,
                'service' => 'weight_calculator',
                'ip' => $request->ip(),
                'date' => now(),
                'only_date' => now()->format('Y-m-d'),
            ];
            $txn = ServiceRecordTxn::create($txnData);
            Log::info('Service record transaction logged', [
                'txn_id' => $txn->id,
                'farmer_id' => $user->id,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => round($weight, 2), // Rounded for cleaner output
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in calculateWeight', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error calculating weight: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function dmiCalculator(Request $request)
    {
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('DmiCalculator auth attempt', [
                'user_id' => $user ? $user->id : null,
                'is_active' => $user ? ($user->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
            ]);

            if (!$user || !$user->is_active) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Check if request has data
            if (!$request->all()) {
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 400);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'lactation' => 'required|string',
                'feed_percentage' => 'required|numeric|min:0|max:100',
                'milk_yield' => 'required|numeric|min:0',
                'weight' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Extract inputs
            $lactation = $request->lactation;
            $feed_percentage = $request->feed_percentage;
            $milk_yield = $request->milk_yield;
            $weight = $request->weight;

            // Calculate DMI and related values
            $dry_matter_intake = (33 / 100 * $milk_yield) + (2 / 100 * $weight);
            $feed = $feed_percentage / 100 * $dry_matter_intake;
            $fodder = $dry_matter_intake - $feed;
            $feed_qty = 100 / 90 * $feed;
            $green_fodder = 60 / 100 * $fodder;
            $maize = 100 / 22 * $green_fodder;
            $barseem = 100 / 17 * $green_fodder;
            $dry_fodder = 40 / 100 * $fodder;
            $hary = 100 / 95 * $dry_fodder;
            $silage_dm = $fodder;
            $silage = 100 / 30 * $silage_dm;

            // Prepare data for response
            $result = [
                'dry_matter_intake' => round($dry_matter_intake, 2),
                'feed' => round($feed, 2),
                'fodder' => round($fodder, 2),
                'feed_qty' => round($feed_qty, 2),
                'green_fodder' => round($green_fodder, 2),
                'maize' => round($maize, 2),
                'barseem' => round($barseem, 2),
                'dry_fodder' => round($dry_fodder, 2),
                'hary' => round($hary, 2),
                'silage_dm' => round($silage_dm, 2),
                'silage' => round($silage, 2),
            ];

            $input = [
                'lactation' => $lactation,
                'feed_percentage' => $feed_percentage,
                'milk_yield' => $milk_yield,
                'weight' => $weight,
            ];

            // Generate HTML
            $html = View::make('pdf.dmi', compact('input', 'result'))->render();

            // Add HTML to response data
            $result['html'] = $html;

            // Update service record
            $serviceRecord = ServiceRecord::first();
            if ($serviceRecord) {
                $serviceRecord->update(['dmi_calculator' => $serviceRecord->dmi_calculator + 1]);
                Log::info('Service record updated for DMI', [
                    'service_record_id' => $serviceRecord->id,
                    'dmi_calculator' => $serviceRecord->dmi_calculator,
                ]);
            } else {
                Log::warning('No service record found in tbl_service_records for DMI');
            }

            // Log transaction
            $txnData = [
                'farmer_id' => $user->id,
                'service' => 'dmi_calculator',
                'ip' => $request->ip(),
                'date' => now(),
                'only_date' => now()->format('Y-m-d'),
            ];
            $txn = ServiceRecordTxn::create($txnData);
            Log::info('Service record transaction logged for DMI', [
                'txn_id' => $txn->id,
                'farmer_id' => $user->id,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in dmiCalculator', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error calculating DMI: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
}
