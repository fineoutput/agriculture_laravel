<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ServiceRecordTxn;
use App\Models\ServiceRecord;
use App\Models\Product;
use App\Models\CheckMyFeedBuy;
use App\Models\Vendor;
use App\Models\Doctor;
use App\Models\Farmer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
// use Barryvdh\DomPDF\Facade as PDF;
// use Barryvdh\DomPDF\PDF as DomPDFPDF;
use Barryvdh\DomPDF\Facade\Pdf;

class FeedController extends Controller
{
    public function calculateWeight(Request $request)
    {
         try {
            $token = $request->header('Authentication');
            if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $user = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$user) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
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
    set_time_limit(300);
    try {
        $token = $request->header('Authentication');
        if (!$token) {
            Log::warning('No bearer token provided');
            return response()->json([
                'message' => 'Token required!',
                'status' => 201,
            ], 401);
        }

        $user = Farmer::where('auth', $token)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            Log::warning('Invalid or inactive user for token', ['token' => $token]);
            return response()->json([
                'message' => 'Invalid token or inactive user!',
                'status' => 201,
            ], 403);
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

        // Render the HTML view
        $htmlContent = view('pdf.dmi', compact('input', 'result'))->render();

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
            'data' => array_merge($result, ['html' => $htmlContent]),
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

    public function feedTest(Request $request)
    {
        try {
            Log::info('FeedTest executed', [
                'ip' => $request->ip(),
            ]);

            // Hardcoded inputs
            $feed_percentage = 45;
            $milk_yield = 12;
            $weight = 25;
            $phase = 'Mid-lactation';

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
                'phase' => $phase,
                'feed_percentage' => $feed_percentage,
                'milk_yield' => $milk_yield,
                'weight' => $weight,
            ];

            // Generate HTML (not included in response)
            $html = View::make('pdf.dmi', compact('input', 'result'))->render();
            Log::info('FeedTest HTML generated', [
                'html_length' => strlen($html),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $result,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in feedTest', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error in feed test: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }




   public function feedCalculator(Request $request)
{
    set_time_limit(300);
    try {
        // Validate authentication token
        $token = $request->header('Authentication');
        if (!$token) {
            Log::warning('No bearer token provided');
            return response()->json([
                'message' => 'Token required!',
                'status' => 201,
            ], 401);
        }

        $user = Farmer::where('auth', $token)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            Log::warning('Invalid or inactive user for token', ['token' => $token]);
            return response()->json([
                'message' => 'Invalid token.',
                'status' => 201,
            ], 403);
        }

        // Validate input data types
        $validator = Validator::make($request->all(), [
            'proteinData' => 'nullable|json',
            'energyData' => 'nullable|json',
            'productData' => 'nullable|json',
            'medicineData' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        // Decode JSON inputs
        $proteinData = $request->proteinData ? json_decode($request->proteinData, true) : [];
        $energyData = $request->energyData ? json_decode($request->energyData, true) : [];
        $productData = $request->productData ? json_decode($request->productData, true) : [];
        $medicineData = $request->medicineData ? json_decode($request->medicineData, true) : [];

        // Log decoded inputs for debugging
        Log::debug('Decoded input data', [
            'ProteinData' => $proteinData,
            'EnergyData' => $energyData,
            'ProductData' => $productData,
            'MedicineData' => $medicineData,
        ]);

        // Validate numeric values in decoded data
        $dataTypes = [
            'ProteinData' => $proteinData,
            'EnergyData' => $energyData,
            'ProductData' => $productData,
            'MedicineData' => $medicineData,
        ];
        foreach ($dataTypes as $type => $data) {
            foreach ($data as $index => $item) {
                if (!empty($item[3])) {
                    if (!is_numeric($item[3])) {
                        Log::error("Non-numeric value in $type at index $index, item[3]", ['value' => $item[3]]);
                        return response()->json([
                            'message' => "Non-numeric value found in $type at index 3",
                            'status' => 201,
                        ], 422);
                    }
                    for ($i = 2; $i <= 15; $i++) {
                        if (isset($item[$i]) && $item[$i] !== '' && $item[$i] !== null && !is_numeric($item[$i])) {
                            Log::error("Non-numeric value in $type at index $index, item[$i]", ['value' => $item[$i]]);
                            return response()->json([
                                'message' => "Non-numeric value found in $type at index $i",
                                'status' => 201,
                            ], 422);
                        }
                    }
                }
            }
        }

        // Initialize nutritional metrics
        $cp = $ee = $cf = $tdn = $me = $ca = $p = $adf = $ndf = $nel = $rudp = $endf = $value = 0;

        // Process ProteinData
        foreach ($proteinData as $item) {
            if (!empty($item[3]) && is_numeric($item[3])) {
                $cp += isset($item[4]) && is_numeric($item[4]) ? (float)$item[4] * (float)$item[3] / 1000 : 0;
                $ee += isset($item[5]) && is_numeric($item[5]) ? (float)$item[5] * (float)$item[3] / 1000 : 0;
                $cf += isset($item[6]) && is_numeric($item[6]) ? (float)$item[6] * (float)$item[3] / 1000 : 0;
                $tdn += isset($item[7]) && is_numeric($item[7]) ? (float)$item[7] * (float)$item[3] / 1000 : 0;
                $me += isset($item[8]) && is_numeric($item[8]) ? (float)$item[8] * (float)$item[3] / 1000 : 0;
                $ca += isset($item[9]) && is_numeric($item[9]) ? (float)$item[9] * (float)$item[3] / 1000 : 0;
                $p += isset($item[10]) && is_numeric($item[10]) ? (float)$item[10] * (float)$item[3] / 1000 : 0;
                $adf += isset($item[11]) && is_numeric($item[11]) ? (float)$item[11] * (float)$item[3] / 1000 : 0;
                $ndf += isset($item[12]) && is_numeric($item[12]) ? (float)$item[12] * (float)$item[3] / 1000 : 0;
                $nel += isset($item[13]) && is_numeric($item[13]) ? (float)$item[13] * (float)$item[3] / 1000 : 0;
                $rudp += isset($item[14]) && is_numeric($item[14]) ? (float)$item[14] * (float)$item[3] / 1000 : 0;
                $endf += isset($item[15]) && is_numeric($item[15]) ? (float)$item[15] * (float)$item[3] / 1000 : 0;
                $value += isset($item[2]) && is_numeric($item[2]) && is_numeric($item[3]) ? (float)$item[2] * (float)$item[3] : 0;
            }
        }

        // Process EnergyData
        foreach ($energyData as $item) {
            if (!empty($item[3]) && is_numeric($item[3])) {
                $cp += isset($item[4]) && is_numeric($item[4]) ? (float)$item[4] * (float)$item[3] / 1000 : 0;
                $ee += isset($item[5]) && is_numeric($item[5]) ? (float)$item[5] * (float)$item[3] / 1000 : 0;
                $cf += isset($item[6]) && is_numeric($item[6]) ? (float)$item[6] * (float)$item[3] / 1000 : 0;
                $tdn += isset($item[7]) && is_numeric($item[7]) ? (float)$item[7] * (float)$item[3] / 1000 : 0;
                $me += isset($item[8]) && is_numeric($item[8]) ? (float)$item[8] * (float)$item[3] / 1000 : 0;
                $ca += isset($item[9]) && is_numeric($item[9]) ? (float)$item[9] * (float)$item[3] / 1000 : 0;
                $p += isset($item[10]) && is_numeric($item[10]) ? (float)$item[10] * (float)$item[3] / 1000 : 0;
                $adf += isset($item[11]) && is_numeric($item[11]) ? (float)$item[11] * (float)$item[3] / 1000 : 0;
                $ndf += isset($item[12]) && is_numeric($item[12]) ? (float)$item[12] * (float)$item[3] / 1000 : 0;
                $nel += isset($item[13]) && is_numeric($item[13]) ? (float)$item[13] * (float)$item[3] / 1000 : 0;
                $rudp += isset($item[14]) && is_numeric($item[14]) ? (float)$item[14] * (float)$item[3] / 1000 : 0;
                $endf += isset($item[15]) && is_numeric($item[15]) ? (float)$item[15] * (float)$item[3] / 1000 : 0;
                $value += isset($item[2]) && is_numeric($item[2]) && is_numeric($item[3]) ? (float)$item[2] * (float)$item[3] : 0;
            }
        }

        // Process ProductData
        foreach ($productData as $item) {
            if (!empty($item[3]) && is_numeric($item[3])) {
                $cp += isset($item[4]) && is_numeric($item[4]) ? (float)$item[4] * (float)$item[3] / 1000 : 0;
                $ee += isset($item[5]) && is_numeric($item[5]) ? (float)$item[5] * (float)$item[3] / 1000 : 0;
                $cf += isset($item[6]) && is_numeric($item[6]) ? (float)$item[6] * (float)$item[3] / 1000 : 0;
                $tdn += isset($item[7]) && is_numeric($item[7]) ? (float)$item[7] * (float)$item[3] / 1000 : 0;
                $me += isset($item[8]) && is_numeric($item[8]) ? (float)$item[8] * (float)$item[3] / 1000 : 0;
                $ca += isset($item[9]) && is_numeric($item[9]) ? (float)$item[9] * (float)$item[3] / 1000 : 0;
                $p += isset($item[10]) && is_numeric($item[10]) ? (float)$item[10] * (float)$item[3] / 1000 : 0;
                $adf += isset($item[11]) && is_numeric($item[11]) ? (float)$item[11] * (float)$item[3] / 1000 : 0;
                $ndf += isset($item[12]) && is_numeric($item[12]) ? (float)$item[12] * (float)$item[3] / 1000 : 0;
                $nel += isset($item[13]) && is_numeric($item[13]) ? (float)$item[13] * (float)$item[3] / 1000 : 0;
                $rudp += isset($item[14]) && is_numeric($item[14]) ? (float)$item[14] * (float)$item[3] / 1000 : 0;
                $endf += isset($item[15]) && is_numeric($item[15]) ? (float)$item[15] * (float)$item[3] / 1000 : 0;
                $value += isset($item[2]) && is_numeric($item[2]) && is_numeric($item[3]) ? (float)$item[2] * (float)$item[3] : 0;
            }
        }

        // Process MedicineData
        foreach ($medicineData as $item) {
            if (!empty($item[3]) && is_numeric($item[3])) {
                $cp += isset($item[4]) && is_numeric($item[4]) ? (float)$item[4] * (float)$item[3] / 1000 : 0;
                $ee += isset($item[5]) && is_numeric($item[5]) ? (float)$item[5] * (float)$item[3] / 1000 : 0;
                $cf += isset($item[6]) && is_numeric($item[6]) ? (float)$item[6] * (float)$item[3] / 1000 : 0;
                $tdn += isset($item[7]) && is_numeric($item[7]) ? (float)$item[7] * (float)$item[3] / 1000 : 0;
                $me += isset($item[8]) && is_numeric($item[8]) ? (float)$item[8] * (float)$item[3] / 1000 : 0;
                $ca += isset($item[9]) && is_numeric($item[9]) ? (float)$item[9] * (float)$item[3] / 1000 : 0;
                $p += isset($item[10]) && is_numeric($item[10]) ? (float)$item[10] * (float)$item[3] / 1000 : 0;
                $adf += isset($item[11]) && is_numeric($item[11]) ? (float)$item[11] * (float)$item[3] / 1000 : 0;
                $ndf += isset($item[12]) && is_numeric($item[12]) ? (float)$item[12] * (float)$item[3] / 1000 : 0;
                $nel += isset($item[13]) && is_numeric($item[13]) ? (float)$item[13] * (float)$item[3] / 1000 : 0;
                $rudp += isset($item[14]) && is_numeric($item[14]) ? (float)$item[14] * (float)$item[3] / 1000 : 0;
                $endf += isset($item[15]) && is_numeric($item[15]) ? (float)$item[15] * (float)$item[3] / 1000 : 0;
                $value += isset($item[2]) && is_numeric($item[2]) && is_numeric($item[3]) ? (float)$item[2] * (float)$item[3] : 0;
            }
        }

        // Calculate fresh basis
        $fresh = [
            'CP' => round($cp, 2),
            'FAT' => round($ee, 2),
            'FIBER' => round($cf, 2),
            'TDN' => round($tdn, 2),
            'ENERGY' => round($me, 2),
            'CA' => round($ca, 2),
            'P' => round($p, 2),
            'RUDP' => round($rudp, 2),
            'ADF' => round($adf, 2),
            'NDF' => round($ndf, 2),
            'NEL' => round((0.0245 * $tdn - 0.12 * 0.454), 2),
            'ENDF' => round($endf, 2),
        ];

        // Calculate DMB
        $dmb_tdn = $tdn > 0 ? round(($tdn * 12 / 100 + $tdn), 2) : 0;
        $dmb = [
            'CP' => $cp > 0 ? round(($cp * 12 / 100 + $cp), 2) : 0,
            'FAT' => $ee > 0 ? round(($ee * 12 / 100 + $ee), 2) : 0,
            'FIBER' => $cf > 0 ? round(($cf * 12 / 100 + $cf), 2) : 0,
            'TDN' => $dmb_tdn,
            'ENERGY' => $me > 0 ? round(($me * 12 / 100 + $me), 2) : 0,
            'CA' => $ca > 0 ? round(($ca * 12 / 100 + $ca), 2) : 0,
            'P' => $p > 0 ? round(($p * 12 / 100 + $p), 2) : 0,
            'RUDP' => $rudp > 0 ? round(($rudp * 12 / 100 + $rudp), 2) : 0,
            'ADF' => $adf > 0 ? round(($adf * 12 / 100 + $adf), 2) : 0,
            'NDF' => $ndf > 0 ? round(($ndf * 12 / 100 + $ndf), 2) : 0,
            'NEL' => round((0.0245 * $dmb_tdn - 0.12 * 0.454), 2),
            'ENDF' => $endf > 0 ? round(($endf * 12 / 100 + $endf), 2) : 0,
        ];

        // Prepare data for the view
        $result = [
            'ProteinData' => json_encode($proteinData),
            'EnergyData' => json_encode($energyData),
            'ProductData' => json_encode($productData),
            'MedicineData' => json_encode($medicineData),
            'fresh' => $fresh,
            'dmb' => $dmb,
            'row_ton' => round($value, 2),
            'row_qtl' => round($value / 10, 2),
        ];

        // Render the HTML view
        $htmlContent = view('pdf.feed', ['result' => $result])->render();

        // Update service record
        $serviceRecord = ServiceRecord::first();
        if ($serviceRecord) {
            $serviceRecord->update(['feed_calculator' => $serviceRecord->feed_calculator + 1]);
            Log::info('Service record updated for FeedCalculator', [
                'service_record_id' => $serviceRecord->id,
                'feed_calculator' => $serviceRecord->feed_calculator,
            ]);
        } else {
            Log::warning('No service record found in tbl_service_records for FeedCalculator');
        }

        // Log transaction
        $txnData = [
            'farmer_id' => $user->id,
            'service' => 'feed_calculator',
            'ip' => $request->ip(),
            'date' => now(),
            'only_date' => now()->format('Y-m-d'),
        ];
        $txn = ServiceRecordTxn::create($txnData);
        Log::info('Service record transaction logged for FeedCalculator', [
            'txn_id' => $txn->id,
            'farmer_id' => $user->id,
        ]);

        // Prepare response
        $send = [
            'fresh' => $fresh,
            'dmb' => $dmb,
            'row_ton' => round($value, 2),
            'row_qtl' => round($value / 10, 2),
            'html' => $htmlContent,
        ];

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $send,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in feedCalculator', [
            'farmer_id' => auth('farmer')->id() ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'Error calculating feed: ' . $e->getMessage(),
            'status' => 201,
        ], 500);
    }
}

    public function animalRequirements(Request $request)
{
    try {
         set_time_limit(300);
        // Authenticate user using 'farmer' guard
        $token = $request->header('Authentication');
        if (!$token) {
            Log::warning('No bearer token provided');
            return response()->json([
                'message' => 'Token required!',
                'status' => 201,
            ], 401);
        }

        $user = Farmer::where('auth', $token)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            Log::warning('Invalid or inactive user for token', ['token' => $token]);
            return response()->json([
                'message' => 'Invalid token or inactive user!',
                'status' => 201,
            ], 403);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'group' => 'required|string',
            'feeding_system' => 'required|string',
            'weight' => 'required|numeric',
            'milk_production' => 'required|numeric',
            'days_milk' => 'required|numeric',
            'milk_fat' => 'required|numeric',
            'milk_protein' => 'required|numeric',
            'milk_lactose' => 'required|numeric',
            'weight_variation' => 'required|numeric',
            'bcs' => 'required|numeric',
            'gestation_days' => 'required|numeric',
            'temp' => 'required|numeric',
            'humidity' => 'required|numeric',
            'thi' => 'required|numeric',
            'fat_4' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        // Extract inputs
        $input = [
            'group' => $request->group,
            'feeding_system' => $request->feeding_system,
            'weight' => $request->weight,
            'milk_production' => $request->milk_production,
            'days_milk' => $request->days_milk,
            'milk_fat' => $request->milk_fat,
            'milk_protein' => $request->milk_protein,
            'milk_lactose' => $request->milk_lactose,
            'weight_variation' => $request->weight_variation,
            'bcs' => $request->bcs,
            'gestation_days' => $request->gestation_days,
            'temp' => $request->temp,
            'humidity' => $request->humidity,
            'thi' => $request->thi,
            'fat_4' => $request->fat_4,
        ];

        Log::info('AnimalRequirements inputs', ['input' => $input]);

        // Placeholder for calculated results (since Excel processing is commented out)
        $result = [
            'dmi_kg_per_day' => null,
            'dmi_percent_bw' => null,
            'drinking_water_liters' => null,
            'drinking_water_percent_bw' => null,
            'net_energy_intake' => null,
            'net_energy_diet' => null,
            'metabolizable_energy_intake' => null,
            'metabolizable_energy_diet' => null,
            'digestible_energy_intake' => null,
            'digestible_energy_diet' => null,
            'tdn_intake' => null,
            'tdn_diet' => null,
            'crude_protein_intake' => null,
            'crude_protein_diet' => null,
            'rdp_intake' => null,
            'rdp_diet' => null,
            'rup_intake' => null,
            'rup_diet' => null,
            'mp_intake' => null,
            'mp_diet' => null,
            'mp_microbial_rumen' => null,
            'digestible_lysine_diet' => null,
            'digestible_methionine_diet' => null,
            'calcium_intake' => null,
            'calcium_diet' => null,
            'phosphorus_intake' => null,
            'phosphorus_diet' => null,
            'sodium_intake' => null,
            'sodium_diet' => null,
            'potassium_intake' => null,
            'potassium_diet' => null,
            'sulfur_intake' => null,
            'sulfur_diet' => null,
            'magnesium_intake' => null,
            'magnesium_diet' => null,
            'zinc_intake' => null,
            'zinc_diet' => null,
            'copper_intake' => null,
            'copper_diet' => null,
            'iron_intake' => null,
            'iron_diet' => null,
            'manganese_intake' => null,
            'manganese_diet' => null,
            'cobalt_intake' => null,
            'cobalt_diet' => null,
            'iodine_intake' => null,
            'iodine_diet' => null,
            'selenium_intake' => null,
            'selenium_diet' => null,
            'chromium_intake' => null,
            'chromium_diet' => null,
            'vitamin_a_diet' => null,
            'vitamin_d_diet' => null,
            'vitamin_e_diet' => null,
            'methane_emission' => null,
        ];

        // Generate PDF
        $farmername = $user->name;
        $pdfName = 'animal_requirements_report_' . Str::uuid() . '.pdf';
        $pdfPath = public_path('feeds/pdf/' . $pdfName);

        // Ensure the directory exists
        if (!file_exists(public_path('feeds/pdf'))) {
            mkdir(public_path('feeds/pdf'), 0777, true);
        }

        // Render the view to PDF
        $pdf = PDF::loadView('pdf.animal_requirements', compact('input', 'result', 'farmername'));
        $pdf->save($pdfPath);

        // Update service record
        $serviceRecord = ServiceRecord::first();
        if ($serviceRecord) {
            $serviceRecord->update(['animal_req' => $serviceRecord->animal_req + 1]);
            Log::info('Service record updated for AnimalRequirements', [
                'service_record_id' => $serviceRecord->id,
                'animal_req' => $serviceRecord->animal_req,
            ]);
        } else {
            Log::warning('No service record found in tbl_service_records for AnimalRequirements');
        }

        // Log transaction
        $txnData = [
            'farmer_id' => $user->id,
            'service' => 'animal_req',
            'ip' => $request->ip(),
            'date' => now(),
            'only_date' => now()->format('Y-m-d'),
        ];
        $txn = ServiceRecordTxn::create($txnData);
        Log::info('Service record transaction logged for AnimalRequirements', [
            'txn_id' => $txn->id,
            'farmer_id' => $user->id,
        ]);

        $pdfUrl = url('feeds/pdf/' . $pdfName);
        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => array_merge($input, $result),
            'pdf_url' => $pdfUrl,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in animalRequirements', [
            'farmer_id' => auth('farmer')->id() ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'Error calculating animal requirements: ' . $e->getMessage(),
            'status' => 201,
        ], 500);
    }
}

public function checkMyFeed(Request $request)
{
    try {
         set_time_limit(300);
        // Authenticate user using 'farmer' guard
        $token = $request->header('Authentication');
        if (!$token) {
            Log::warning('No bearer token provided');
            return response()->json([
                'message' => 'Token required!',
                'status' => 201,
            ], 401);
        }

        $user = Farmer::where('auth', $token)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            Log::warning('Invalid or inactive user for token', ['token' => $token]);
            return response()->json([
                'message' => 'Invalid token or inactive user!',
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
            'live_weight' => 'nullable|numeric',
            'pregnancy' => 'nullable|numeric',
            'milk_yield_volume' => 'nullable|numeric',
            'milk_yield_fat' => 'nullable|numeric',
            'milk_yield_protein' => 'nullable|numeric',
            'live_weight_gain' => 'nullable|numeric',
            'milk_return' => 'nullable|numeric',
            'material' => 'required|string', // Ensure material is a string (JSON)
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        // Decode material JSON
        $rawMaterial = $request->material;
        Log::debug('Raw material input', ['material' => $rawMaterial]);
        $material = json_decode($rawMaterial, true);

        // Check if JSON decoding failed or result is not an array
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid material JSON format', ['material' => $rawMaterial, 'json_error' => json_last_error_msg()]);
            return response()->json([
                'message' => 'Invalid material JSON format: ' . json_last_error_msg(),
                'status' => 201,
            ], 422);
        }

        // Ensure material is an array; convert single object to array if needed
        if (!is_array($material)) {
            Log::warning('Material is not an array, attempting to wrap as array', ['material' => $material]);
            $material = [$material];
        }

        // Handle empty material array
        if (empty($material)) {
            Log::warning('Material array is empty', ['material' => $rawMaterial]);
            $material = [];
        }

        // Validate material structure
        foreach ($material as $index => $mat) {
            if (!is_array($mat) || !isset($mat['fresh']) || !is_numeric($mat['fresh'])) {
                Log::error('Invalid material structure at index ' . $index, ['material' => $mat]);
                return response()->json([
                    'message' => 'Each material must be an array with a numeric fresh value',
                    'status' => 201,
                ], 422);
            }
        }

        $input = [
            'lactation' => $request->lactation,
            'live_weight' => $request->live_weight,
            'pregnancy' => $request->pregnancy,
            'milk_yield_volume' => $request->milk_yield_volume,
            'milk_yield_fat' => $request->milk_yield_fat,
            'milk_yield_protein' => $request->milk_yield_protein,
            'live_weight_gain' => $request->live_weight_gain,
            'milk_return' => $request->milk_return,
            'material' => $material,
        ];

        Log::info('CheckMyFeed inputs', ['input' => $input]);

        // Calculate total intake
        $totalIntake = 0;
        if (!empty($material)) {
            $totalIntake = array_sum(array_column($material, 'fresh'));
        }

        // Placeholder for calculated data (since Excel processing is commented out)
        $result = [
            'metabolisable_energy_needs' => null,
            'metabolisable_energy_intake' => null,
            'crude_protein_needs' => null,
            'crude_protein_intake' => null,
            'calcium_needs' => null,
            'calcium_intake' => null,
            'phosphorus_needs' => null,
            'phosphorus_intake' => null,
            'ndf_needs' => null,
            'ndf_intake' => null,
            'dry_matter_max' => null,
            'dry_matter_intake' => null,
            'concentrate_max' => null,
            'concentrate_intake' => null,
            'milk_return_kg' => $input['milk_return'],
            'milk_return_day' => null,
            'feed_cost_day' => null,
            'mifc_roi_day' => null,
            'total_intake' => $totalIntake,
        ];

        // Generate PDF
        $farmername = $user->name;
        $pdfName = 'check_my_feed_report_' . Str::uuid() . '.pdf';
        $pdfPath = public_path('feeds/pdf/' . $pdfName);

        // Ensure the directory exists
        if (!file_exists(public_path('feeds/pdf'))) {
            mkdir(public_path('feeds/pdf'), 0777, true);
        }

        // Render the view to PDF
        $pdf = PDF::loadView('pdf.check_my_feed', compact('input', 'result', 'farmername'));
        $pdf->save($pdfPath);

        // Update service record
        $serviceRecord = ServiceRecord::first();
        if ($serviceRecord) {
            $serviceRecord->update(['check_my_feed' => $serviceRecord->check_my_feed + 1]);
            Log::info('Service record updated for CheckMyFeed', [
                'service_record_id' => $serviceRecord->id,
                'check_my_feed' => $serviceRecord->check_my_feed,
            ]);
        } else {
            Log::warning('No service record found in tbl_service_records for CheckMyFeed');
        }

        // Log transaction
        $txnData = [
            'farmer_id' => $user->id,
            'service' => 'check_my_feed',
            'ip' => $request->ip(),
            'date' => now(),
            'only_date' => now()->format('Y-m-d'),
        ];
        $txn = ServiceRecordTxn::create($txnData);
        Log::info('Service record transaction logged for CheckMyFeed', [
            'txn_id' => $txn->id,
            'farmer_id' => $user->id,
        ]);

        $pdfUrl = url('feeds/pdf/' . $pdfName);
        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $result,
            'pdf_url' => $pdfUrl,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in checkMyFeed', [
            'farmer_id' => auth('farmer')->id() ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'Error checking feed: ' . $e->getMessage(),
            'status' => 201,
        ], 500);
    }
}

    public function dairyMart(Request $request)
    {
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('DairyMart auth attempt', [
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

            // Fetch active products
            $products = Product::where('is_active', 1)->get();
            Log::info('DairyMart products fetched', ['count' => $products->count()]);

            // Process products
            $data = $products->map(function ($product) {
                // Handle image URL
                $image = $product->image ? asset($product->image) : '';

                return [
                    'name_english' => $product->name_english,
                    'name_hindi' => $product->name_hindi,
                    'name_punjabi' => $product->name_punjabi,
                    'description_english' => $product->description_english,
                    'description_hindi' => $product->description_hindi,
                    'description_punjabi' => $product->description_punjabi,
                    'min_qty' => $product->min_qty ?? 1,
                    'image1' => $product->image1 ? asset($product->image1) : '',
                    'image2' => $product->image2 ? asset($product->image2) : '',
                    'mrp' => $product->mrp,
                    'selling_price' => $product->selling_price,
                    'inventory' => $product->inventory,
                ];
            })->toArray();

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in dairyMart', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching products: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
    
    public function doctorOnCall(Request $request)
    {
        try {
            // Fetch active doctors
            $doctors = Doctor::where('is_active2', 0)->get();
            Log::info('DoctorOnCall doctors fetched', ['count' => $doctors->count()]);

            // Process doctors
            $data = $doctors->map(function ($doctor) {
                // Handle image URL
                $image = $doctor->image ? asset($doctor->image) : '';

                return [
                    'name_english' => $doctor->name_english,
                    'name_hindi' => $doctor->name_hindi,
                    'name_punjabi' => $doctor->name_punjabi,
                    'email' => $doctor->email,
                    'degree_english' => $doctor->degree_english,
                    'degree_hindi' => $doctor->degree_hindi,
                    'degree_punjabi' => $doctor->degree_punjabi,
                    'image' => $image,
                ];
            })->toArray();

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in doctorOnCall', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching doctors: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function expertAdvice(Request $request)
    {
        try {
            
            $experts = Doctor::where('is_active2', 1)->get();
            Log::info('ExpertAdvice experts fetched', ['count' => $experts->count()]);

            // Process experts
            $data = $experts->map(function ($expert) {
                // Handle image URL
                $image = $expert->image ? asset($expert->image) : '';

                return [
                    'name_english' => $expert->name_english,
                    'name_hindi' => $expert->name_hindi,
                    'name_punjabi' => $expert->name_punjabi,
                    'email' => $expert->email,
                    'type' => $expert->type,
                    'degree_english' => $expert->degree_english,
                    'degree_hindi' => $expert->degree_hindi,
                    'degree_punjabi' => $expert->degree_punjabi,
                    'education_qualification' => $expert->education_qualification,
                    'city' => $expert->city,
                    'state' => $expert->state,
                    'phone_number' => $expert->phone_number,
                    'image' => $image,
                ];
            })->toArray();

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in expertAdvice', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching experts: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function radiusVendor(Request $request)
    {
        try {
            // Fetch active vendors
            
            $vendors = Vendor::where('is_active', 0)->get();
            Log::info('RadiusVendor vendors fetched', ['count' => $vendors->count()]);

            // Process vendors
            $data = $vendors->map(function ($vendor) {
                return [
                    'name_english' => $vendor->name_english,
                    'name_hindi' => $vendor->name_hindi,
                    'name_punjabi' => $vendor->name_punjabi,
                    'shop_name_english' => $vendor->shop_name_english,
                    'shop_name_hindi' => $vendor->shop_name_hindi,
                    'shop_name_punjabi' => $vendor->shop_name_punjabi,
                    'address_english' => $vendor->address_english,
                    'address_hindi' => $vendor->address_hindi,
                    'address_punjabi' => $vendor->address_punjabi,
                    'district_english' => $vendor->district_english,
                    'district_hindi' => $vendor->district_hindi,
                    'district_punjabi' => $vendor->district_punjabi,
                    'city' => $vendor->city,
                    'state' => $vendor->state,
                    'pincode' => $vendor->pincode,
                ];
            })->toArray();

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in radiusVendor', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching vendors: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function buyFeed(Request $request)
    {
        try {
            // Authenticate farmer using 'farmer' guard
            $farmer = auth('farmer')->user();
            Log::info('BuyFeed auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? ($farmer->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
            ]);

            if (!$farmer || !$farmer->is_active) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Check for existing paid plan
            $existingPlan = CheckMyFeedBuy::where('farmer_id', $farmer->id)
                ->where('payment_status', 1)
                ->first();

            if ($existingPlan) {
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 400);
            }

            // Generate transaction
            $txnId = mt_rand(999999, 999999999999);
            $currentDate = now(new DateTimeZone('Asia/Kolkata'))->format('Y-m-d H:i:s');

            $transaction = CheckMyFeedBuy::create([
                'farmer_id' => $farmer->id,
                'price' => config('ccavenue.feed_amount'),
                'payment_status' => 0,
                'txn_id' => $txnId,
                'date' => $currentDate,
                'gateway' => 'CC Avenue',
            ]);

            // Prepare CCAvenue payment data
            $successUrl = url('api/feed-payment-success');
            $failUrl = url('api/payment-failed');
            $post = [
                'txn_id' => '',
                'merchant_id' => config('ccavenue.merchant_id'),
                'order_id' => $txnId,
                'amount' => config('ccavenue.feed_amount'),
                'currency' => 'INR',
                'redirect_url' => $successUrl,
                'cancel_url' => $failUrl,
                'billing_name' => $farmer->name ?? 'Unknown',
                'billing_address' => $farmer->village ?? '',
                'billing_city' => $farmer->city ?? '',
                'billing_state' => $farmer->state ?? '',
                'billing_zip' => $farmer->pincode ?? '',
                'billing_country' => 'India',
                'billing_tel' => $farmer->phone ?? '',
                'billing_email' => '',
                'merchant_param1' => 'Feed Payment',
            ];

            // Encrypt data for CCAvenue
            $merchantData = '';
            foreach ($post as $key => $value) {
                $merchantData .= $key . '=' . $value . '&';
            }
            $merchantData = rtrim($merchantData, '&');

            $workingKey = config('ccavenue.working_key');
            $accessCode = config('ccavenue.access_code');

            // Convert working key to binary
            $key = pack('H*', md5($workingKey));
            $iv = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
            $encryptedData = openssl_encrypt($merchantData, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
            $encryptedDataHex = bin2hex($encryptedData);

            // Prepare response
            $responseData = [
                'order_id' => $transaction->id,
                'access_code' => $accessCode,
                'redirect_url' => $successUrl,
                'cancel_url' => $failUrl,
                'enc_val' => $encryptedDataHex,
                'plain' => $merchantData,
                'merchant_param1' => 'Feed Payment',
            ];

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $responseData,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in buyFeed', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error processing payment: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
}
