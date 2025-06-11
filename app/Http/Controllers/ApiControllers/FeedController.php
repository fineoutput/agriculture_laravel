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
use DateTimeZone;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;



use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

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




//    public function feedCalculator(Request $request)
// {
//     set_time_limit(300);
//     try {
//         // Validate authentication token
//         $token = $request->header('Authentication');
//         if (!$token) {
//             Log::warning('No bearer token provided');
//             return response()->json([
//                 'message' => 'Token required!',
//                 'status' => 201,
//             ], 401);
//         }

//         $user = Farmer::where('auth', $token)
//             ->where('is_active', 1)
//             ->first();

//         if (!$user) {
//             Log::warning('Invalid or inactive user for token', ['token' => $token]);
//             return response()->json([
//                 'message' => 'Invalid token.',
//                 'status' => 201,
//             ], 403);
//         }

//         // Validate input data types
//         $validator = Validator::make($request->all(), [
//             'proteinData' => 'nullable|json',
//             'energyData' => 'nullable|json',
//             'productData' => 'nullable|json',
//             'medicineData' => 'nullable|json',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'message' => $validator->errors()->first(),
//                 'status' => 201,
//             ], 422);
//         }

//         // Decode JSON inputs
//         $proteinData = $request->proteinData ? json_decode($request->proteinData, true) : [];
//         $energyData = $request->energyData ? json_decode($request->energyData, true) : [];
//         $productData = $request->productData ? json_decode($request->productData, true) : [];
//         $medicineData = $request->medicineData ? json_decode($request->medicineData, true) : [];

//         // Log decoded inputs for debugging
//         Log::debug('Decoded input data', [
//             'ProteinData' => $proteinData,
//             'EnergyData' => $energyData,
//             'ProductData' => $productData,
//             'MedicineData' => $medicineData,
//         ]);

//         // Validate numeric values in decoded data
//         $dataTypes = [
//             'ProteinData' => $proteinData,
//             'EnergyData' => $energyData,
//             'ProductData' => $productData,
//             'MedicineData' => $medicineData,
//         ];
//         foreach ($dataTypes as $type => $data) {
//             foreach ($data as $index => $item) {
//                 if (!empty($item[3])) {
//                     if (!is_numeric($item[3])) {
//                         Log::error("Non-numeric value in $type at index $index, item[3]", ['value' => $item[3]]);
//                         return response()->json([
//                             'message' => "Non-numeric value found in $type at index 3",
//                             'status' => 201,
//                         ], 422);
//                     }
//                     for ($i = 2; $i <= 15; $i++) {
//                         if (isset($item[$i]) && $item[$i] !== '' && $item[$i] !== null && !is_numeric($item[$i])) {
//                             Log::error("Non-numeric value in $type at index $index, item[$i]", ['value' => $item[$i]]);
//                             return response()->json([
//                                 'message' => "Non-numeric value found in $type at index $i",
//                                 'status' => 201,
//                             ], 422);
//                         }
//                     }
//                 }
//             }
//         }

//         // Initialize nutritional metrics
//         $cp = $ee = $cf = $tdn = $me = $ca = $p = $adf = $ndf = $nel = $rudp = $endf = $value = 0;

//         // Process ProteinData
//         foreach ($proteinData as $item) {
//             if (!empty($item[3]) && is_numeric($item[3])) {
//                 $cp += isset($item[4]) && is_numeric($item[4]) ? (float)$item[4] * (float)$item[3] / 1000 : 0;
//                 $ee += isset($item[5]) && is_numeric($item[5]) ? (float)$item[5] * (float)$item[3] / 1000 : 0;
//                 $cf += isset($item[6]) && is_numeric($item[6]) ? (float)$item[6] * (float)$item[3] / 1000 : 0;
//                 $tdn += isset($item[7]) && is_numeric($item[7]) ? (float)$item[7] * (float)$item[3] / 1000 : 0;
//                 $me += isset($item[8]) && is_numeric($item[8]) ? (float)$item[8] * (float)$item[3] / 1000 : 0;
//                 $ca += isset($item[9]) && is_numeric($item[9]) ? (float)$item[9] * (float)$item[3] / 1000 : 0;
//                 $p += isset($item[10]) && is_numeric($item[10]) ? (float)$item[10] * (float)$item[3] / 1000 : 0;
//                 $adf += isset($item[11]) && is_numeric($item[11]) ? (float)$item[11] * (float)$item[3] / 1000 : 0;
//                 $ndf += isset($item[12]) && is_numeric($item[12]) ? (float)$item[12] * (float)$item[3] / 1000 : 0;
//                 $nel += isset($item[13]) && is_numeric($item[13]) ? (float)$item[13] * (float)$item[3] / 1000 : 0;
//                 $rudp += isset($item[14]) && is_numeric($item[14]) ? (float)$item[14] * (float)$item[3] / 1000 : 0;
//                 $endf += isset($item[15]) && is_numeric($item[15]) ? (float)$item[15] * (float)$item[3] / 1000 : 0;
//                 $value += isset($item[2]) && is_numeric($item[2]) && is_numeric($item[3]) ? (float)$item[2] * (float)$item[3] : 0;
//             }
//         }

//         // Process EnergyData
//         foreach ($energyData as $item) {
//             if (!empty($item[3]) && is_numeric($item[3])) {
//                 $cp += isset($item[4]) && is_numeric($item[4]) ? (float)$item[4] * (float)$item[3] / 1000 : 0;
//                 $ee += isset($item[5]) && is_numeric($item[5]) ? (float)$item[5] * (float)$item[3] / 1000 : 0;
//                 $cf += isset($item[6]) && is_numeric($item[6]) ? (float)$item[6] * (float)$item[3] / 1000 : 0;
//                 $tdn += isset($item[7]) && is_numeric($item[7]) ? (float)$item[7] * (float)$item[3] / 1000 : 0;
//                 $me += isset($item[8]) && is_numeric($item[8]) ? (float)$item[8] * (float)$item[3] / 1000 : 0;
//                 $ca += isset($item[9]) && is_numeric($item[9]) ? (float)$item[9] * (float)$item[3] / 1000 : 0;
//                 $p += isset($item[10]) && is_numeric($item[10]) ? (float)$item[10] * (float)$item[3] / 1000 : 0;
//                 $adf += isset($item[11]) && is_numeric($item[11]) ? (float)$item[11] * (float)$item[3] / 1000 : 0;
//                 $ndf += isset($item[12]) && is_numeric($item[12]) ? (float)$item[12] * (float)$item[3] / 1000 : 0;
//                 $nel += isset($item[13]) && is_numeric($item[13]) ? (float)$item[13] * (float)$item[3] / 1000 : 0;
//                 $rudp += isset($item[14]) && is_numeric($item[14]) ? (float)$item[14] * (float)$item[3] / 1000 : 0;
//                 $endf += isset($item[15]) && is_numeric($item[15]) ? (float)$item[15] * (float)$item[3] / 1000 : 0;
//                 $value += isset($item[2]) && is_numeric($item[2]) && is_numeric($item[3]) ? (float)$item[2] * (float)$item[3] : 0;
//             }
//         }

//         // Process ProductData
//         foreach ($productData as $item) {
//             if (!empty($item[3]) && is_numeric($item[3])) {
//                 $cp += isset($item[4]) && is_numeric($item[4]) ? (float)$item[4] * (float)$item[3] / 1000 : 0;
//                 $ee += isset($item[5]) && is_numeric($item[5]) ? (float)$item[5] * (float)$item[3] / 1000 : 0;
//                 $cf += isset($item[6]) && is_numeric($item[6]) ? (float)$item[6] * (float)$item[3] / 1000 : 0;
//                 $tdn += isset($item[7]) && is_numeric($item[7]) ? (float)$item[7] * (float)$item[3] / 1000 : 0;
//                 $me += isset($item[8]) && is_numeric($item[8]) ? (float)$item[8] * (float)$item[3] / 1000 : 0;
//                 $ca += isset($item[9]) && is_numeric($item[9]) ? (float)$item[9] * (float)$item[3] / 1000 : 0;
//                 $p += isset($item[10]) && is_numeric($item[10]) ? (float)$item[10] * (float)$item[3] / 1000 : 0;
//                 $adf += isset($item[11]) && is_numeric($item[11]) ? (float)$item[11] * (float)$item[3] / 1000 : 0;
//                 $ndf += isset($item[12]) && is_numeric($item[12]) ? (float)$item[12] * (float)$item[3] / 1000 : 0;
//                 $nel += isset($item[13]) && is_numeric($item[13]) ? (float)$item[13] * (float)$item[3] / 1000 : 0;
//                 $rudp += isset($item[14]) && is_numeric($item[14]) ? (float)$item[14] * (float)$item[3] / 1000 : 0;
//                 $endf += isset($item[15]) && is_numeric($item[15]) ? (float)$item[15] * (float)$item[3] / 1000 : 0;
//                 $value += isset($item[2]) && is_numeric($item[2]) && is_numeric($item[3]) ? (float)$item[2] * (float)$item[3] : 0;
//             }
//         }

//         // Process MedicineData
//         foreach ($medicineData as $item) {
//             if (!empty($item[3]) && is_numeric($item[3])) {
//                 $cp += isset($item[4]) && is_numeric($item[4]) ? (float)$item[4] * (float)$item[3] / 1000 : 0;
//                 $ee += isset($item[5]) && is_numeric($item[5]) ? (float)$item[5] * (float)$item[3] / 1000 : 0;
//                 $cf += isset($item[6]) && is_numeric($item[6]) ? (float)$item[6] * (float)$item[3] / 1000 : 0;
//                 $tdn += isset($item[7]) && is_numeric($item[7]) ? (float)$item[7] * (float)$item[3] / 1000 : 0;
//                 $me += isset($item[8]) && is_numeric($item[8]) ? (float)$item[8] * (float)$item[3] / 1000 : 0;
//                 $ca += isset($item[9]) && is_numeric($item[9]) ? (float)$item[9] * (float)$item[3] / 1000 : 0;
//                 $p += isset($item[10]) && is_numeric($item[10]) ? (float)$item[10] * (float)$item[3] / 1000 : 0;
//                 $adf += isset($item[11]) && is_numeric($item[11]) ? (float)$item[11] * (float)$item[3] / 1000 : 0;
//                 $ndf += isset($item[12]) && is_numeric($item[12]) ? (float)$item[12] * (float)$item[3] / 1000 : 0;
//                 $nel += isset($item[13]) && is_numeric($item[13]) ? (float)$item[13] * (float)$item[3] / 1000 : 0;
//                 $rudp += isset($item[14]) && is_numeric($item[14]) ? (float)$item[14] * (float)$item[3] / 1000 : 0;
//                 $endf += isset($item[15]) && is_numeric($item[15]) ? (float)$item[15] * (float)$item[3] / 1000 : 0;
//                 $value += isset($item[2]) && is_numeric($item[2]) && is_numeric($item[3]) ? (float)$item[2] * (float)$item[3] : 0;
//             }
//         }

//         // Calculate fresh basis
//         $fresh = [
//             'CP' => round($cp, 2),
//             'FAT' => round($ee, 2),
//             'FIBER' => round($cf, 2),
//             'TDN' => round($tdn, 2),
//             'ENERGY' => round($me, 2),
//             'CA' => round($ca, 2),
//             'P' => round($p, 2),
//             'RUDP' => round($rudp, 2),
//             'ADF' => round($adf, 2),
//             'NDF' => round($ndf, 2),
//             'NEL' => round((0.0245 * $tdn - 0.12 * 0.454), 2),
//             'ENDF' => round($endf, 2),
//         ];

//         // Calculate DMB
//         $dmb_tdn = $tdn > 0 ? round(($tdn * 12 / 100 + $tdn), 2) : 0;
//         $dmb = [
//             'CP' => $cp > 0 ? round(($cp * 12 / 100 + $cp), 2) : 0,
//             'FAT' => $ee > 0 ? round(($ee * 12 / 100 + $ee), 2) : 0,
//             'FIBER' => $cf > 0 ? round(($cf * 12 / 100 + $cf), 2) : 0,
//             'TDN' => $dmb_tdn,
//             'ENERGY' => $me > 0 ? round(($me * 12 / 100 + $me), 2) : 0,
//             'CA' => $ca > 0 ? round(($ca * 12 / 100 + $ca), 2) : 0,
//             'P' => $p > 0 ? round(($p * 12 / 100 + $p), 2) : 0,
//             'RUDP' => $rudp > 0 ? round(($rudp * 12 / 100 + $rudp), 2) : 0,
//             'ADF' => $adf > 0 ? round(($adf * 12 / 100 + $adf), 2) : 0,
//             'NDF' => $ndf > 0 ? round(($ndf * 12 / 100 + $ndf), 2) : 0,
//             'NEL' => round((0.0245 * $dmb_tdn - 0.12 * 0.454), 2),
//             'ENDF' => $endf > 0 ? round(($endf * 12 / 100 + $endf), 2) : 0,
//         ];

//         // Prepare data for the view
//         $result = [
//             'ProteinData' => json_encode($proteinData),
//             'EnergyData' => json_encode($energyData),
//             'ProductData' => json_encode($productData),
//             'MedicineData' => json_encode($medicineData),
//             'fresh' => $fresh,
//             'dmb' => $dmb,
//             'row_ton' => round($value, 2),
//             'row_qtl' => round($value / 10, 2),
//         ];

//         // Render the HTML view
//         $htmlContent = view('pdf.feed', ['result' => $result])->render();

//         // Update service record
//         $serviceRecord = ServiceRecord::first();
//         if ($serviceRecord) {
//             $serviceRecord->update(['feed_calculator' => $serviceRecord->feed_calculator + 1]);
//             Log::info('Service record updated for FeedCalculator', [
//                 'service_record_id' => $serviceRecord->id,
//                 'feed_calculator' => $serviceRecord->feed_calculator,
//             ]);
//         } else {
//             Log::warning('No service record found in tbl_service_records for FeedCalculator');
//         }

//         // Log transaction
//         $txnData = [
//             'farmer_id' => $user->id,
//             'service' => 'feed_calculator',
//             'ip' => $request->ip(),
//             'date' => now(),
//             'only_date' => now()->format('Y-m-d'),
//         ];
//         $txn = ServiceRecordTxn::create($txnData);
//         Log::info('Service record transaction logged for FeedCalculator', [
//             'txn_id' => $txn->id,
//             'farmer_id' => $user->id,
//         ]);

//         // Prepare response
//         $send = [
//             'fresh' => $fresh,
//             'dmb' => $dmb,
//             'row_ton' => round($value, 2),
//             'row_qtl' => round($value / 10, 2),
//             'html' => $htmlContent,
//         ];

//         return response()->json([
//             'message' => 'Success!',
//             'status' => 200,
//             'data' => $send,
//         ], 200);

//     } catch (\Exception $e) {
//         Log::error('Error in feedCalculator', [
//             'farmer_id' => auth('farmer')->id() ?? null,
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString(),
//         ]);

//         return response()->json([
//             'message' => 'Error calculating feed: ' . $e->getMessage(),
//             'status' => 201,
//         ], 500);
//     }
// }



// test feed cotroller
public function feed_calculator(Request $request)
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
                'message' => 'Invalid token.',
                'status' => 201,
            ], 403);
        }

 
        $validator = Validator::make($request->all(), [
            'ProteinData' => 'nullable|json',
            'EnergyData' => 'nullable|json',
            'ProductData' => 'nullable|json',
            'MedicineData' => 'nullable|json',
        ]);
 
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }
 
        $proteinData = $request->ProteinData ? json_decode($request->ProteinData, true) : [];
        $energyData = $request->EnergyData ? json_decode($request->EnergyData, true) : [];
        $productData = $request->ProductData ? json_decode($request->ProductData, true) : [];
        $medicineData = $request->MedicineData ? json_decode($request->MedicineData, true) : [];
 
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
                     
                        return response()->json([
                            'message' => "Non-numeric value found in $type at index 3",
                            'status' => 201,
                        ], 422);
                    }
                    for ($i = 2; $i <= 15; $i++) {
                        if (isset($item[$i]) && $item[$i] !== '' && $item[$i] !== null && !is_numeric($item[$i])) {
                         
                            return response()->json([
                                'message' => "Non-numeric value found in $type at index $i",
                                'status' => 201,
                            ], 422);
                        }
                    }
                }
            }
        }
 
        $cp = $ee = $cf = $tdn = $me = $ca = $p = $adf = $ndf = $nel = $rudp = $endf = $value = 0;
 
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
 
        $result = [
            'fresh' => $fresh,
            'dmb' => $dmb,
            'row_ton' => round($value, 2),
            'row_qtl' => round($value / 10, 2),
            'ProteinData' => $request->ProteinData,
            'EnergyData' => $request->EnergyData,
            'ProductData' => $request->ProductData,
            'MedicineData' => $request->MedicineData,
        ];
 
 
 
        // if ($request->query('download') === 'pdf') {
        //     $pdf = PDF::loadView('partner/pdf.feed', compact('result'))
        //         ->setPaper('a4', 'portrait')
        //         ->setOptions(['defaultFont' => 'sans-serif']);
        //     return $pdf->download('feed_calculation_' . now()->format('YmdHis') . '.pdf');
        // }
               // Handle PDF file upload
 
           $feedPdfName = 'feed_report_' . Str::uuid() . '.pdf';
            $pdfPath = public_path('feeds/pdf/' . $feedPdfName);
 
            // Ensure the directory exists
            if (!file_exists(public_path('feeds/pdf'))) {
                mkdir(public_path('feeds/pdf'), 0777, true);
            }
 
         $htmlContent = view('pdf.feed', ['result' => $result])->render();
 
        // $pdf->save($pdfPath);
 
        $send = [
            'fresh' => $fresh,
            'dmb' => $dmb,
            'row_ton' => round($value, 2),
            'row_qtl' => round($value / 10, 2),
        ];
 
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
        $pdfUrl = url('feeds/pdf/' . $feedPdfName);
 
        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $send,
             'html' => $htmlContent,
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

// // Animal Req
//   public function animalRequirements(Request $request)
//     {
//         try {
//             set_time_limit(300);
 
//             if (!$request->isMethod('post')) {
//                 return response()->json([
//                     'message' => 'Please Insert Data',
//                     'status' => 201,
//                 ], 201);
//             }

//             $token = $request->header('Authentication');
//             if (!$token) {
//                 Log::warning('No bearer token provided');
//                 return response()->json([
//                     'message' => 'Token required!',
//                     'status' => 201,
//                 ], 401);
//             }

//             $farmer = Farmer::where('auth', $token)->where('is_active', 1)->first();
//             if (!$farmer) {
//                 Log::warning('Invalid or inactive farmer for token', ['token' => substr($token, 0, 10) . '...']);
//                 return response()->json([
//                     'message' => 'Permission Denied!',
//                     'status' => 201,
//                 ], 403);
//             }

//            $validator = Validator::make($request->all(), [
//                 'group' => 'required',
//                 'feeding_system' => 'required',
//                 'weight' => 'required', 
//                 'milk_production' => 'required', 
//                 'days_milk' => 'required', 
//                 'milk_fat' => 'required', 
//                 'milk_protein' => 'required', 
//                 'milk_lactose' => 'required', 
//                 'weight_variation' => 'required', 
//                 'bcs' => 'required', 
//                 'gestation_days' => 'required', 
//                 'temp' => 'required', 
//                 'humidity' => 'required',
//                 'thi' => 'required', 
//                 'fat_4' => 'required',
//             ]);

//             if ($validator->fails()) {
//                 return response()->json([
//                     'message' => $validator->errors()->all(),
//                     'status' => 201,
//                 ], 422);
//             }

//             $input = $request->only([
//                 'group',
//                 'feeding_system',
//                 'weight',
//                 'milk_production',
//                 'days_milk',
//                 'milk_fat',
//                 'milk_protein',
//                 'milk_lactose',
//                 'weight_variation',
//                 'bcs',
//                 'gestation_days',
//                 'temp',
//                 'humidity',
//                 'thi',
//                 'fat_4',
//             ]);

//             Log::info('AnimalRequirements Input: ', $input);

//             $inputFileName = public_path('assets/excel/animal_requirement.xlsx');
//             if (!file_exists($inputFileName)) {
//                 Log::error('Excel file not found', ['path' => $inputFileName]);
//                 return response()->json([
//                     'message' => 'Excel template not found!',
//                     'status' => 500,
//                 ], 500);
//             }

//             $spreadsheet = IOFactory::load($inputFileName);
//             $sheet = $spreadsheet->getActiveSheet();

//             Log::info('Setting cell values', [$input]);
//             $sheet->setCellValue('F21', (string)$input['group']);
//             $sheet->setCellValue('F22', (string)$input['feeding_system']);
//             $sheet->setCellValue('F23', (float)$input['weight']);
//             $sheet->setCellValue('F24', (float)$input['milk_production']);
//             $sheet->setCellValue('F25', (float)$input['days_milk']);
//             $sheet->setCellValue('F26', (float)$input['milk_fat']);
//             $sheet->setCellValue('F27', (float)$input['milk_protein']);
//             $sheet->setCellValue('F28', (float)$input['milk_lactose']);
//             $sheet->setCellValue('I21', (float)$input['weight_variation']);
//             $sheet->setCellValue('I22', (float)$input['bcs']);
//             $sheet->setCellValue('I23', (float)$input['gestation_days']);
//             $sheet->setCellValue('I24', (float)$input['temp']);
//             $sheet->setCellValue('I25', (float)$input['humidity']);
//             $sheet->setCellValue('I26', (float)$input['thi']);
//             $sheet->setCellValue('I27', (float)$input['fat_4']);

//             $inputCells = ['F21', 'F22', 'F23', 'F24', 'F25', 'F26', 'F27', 'F28', 'I21', 'I22', 'I23', 'I24', 'I25', 'I26', 'I27'];
//             $cellValues = [];
//             foreach ($inputCells as $cell) {
//                 $value = $sheet->getCell($cell)->getValue();
//                 $type = gettype($value);
//                 $cellValues[$cell] = ['value' => $value, 'type' => $type];
//             }
//             Log::info('Input cell values and types', $cellValues);

//             Log::info('Saving and recalculating spreadsheet');
//             $outputFileName = public_path('assets/excel/animal_requirement_output.xlsx');
//             $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
//             $writer->setPreCalculateFormulas(true);
//             $spreadsheet->getCalculationEngine()->disableCalculationCache();
//             $spreadsheet->getCalculationEngine()->calculate();
//             $writer->save($outputFileName);

//             $spreadsheet = IOFactory::load($outputFileName);
//             $sheet = $spreadsheet->getActiveSheet();
//             $energyCell = 'J30'; 
//             $proteinCell = 'K31'; 
//             $rawValue1 = $sheet->getCell($energyCell)->getValue();
//             $rawValue2 = $sheet->getCell($proteinCell)->getValue();
//             $rawType1 = gettype($rawValue1);
//             $rawType2 = gettype($rawValue2);
//             Log::info('Raw cell values and types', [
//                 $energyCell => ['value' => $rawValue1, 'type' => $rawType1],
//                 $proteinCell => ['value' => $rawValue2, 'type' => $rawType2]
//             ]);

//             $formulaResult1 = $sheet->getCell($energyCell)->getCalculatedValue();
//             $formulaResult2 = $sheet->getCell($proteinCell)->getCalculatedValue();
//             $calcType1 = gettype($formulaResult1);
//             $calcType2 = gettype($formulaResult2);
//             Log::info('Calculated values and types', [
//                 $energyCell => ['value' => $formulaResult1, 'type' => $calcType1],
//                 $proteinCell => ['value' => $formulaResult2, 'type' => $calcType2]
//             ]);

//             $results = [
//                 'energy' => $formulaResult1 ?? 0,
//                 'protein' => $formulaResult2 ?? 0,
//             ];
//             if ($formulaResult1 === null || $formulaResult2 === null) {
//                 Log::warning('Formula results are null', [
//                     $energyCell => ['raw_value' => $rawValue1, 'raw_type' => $rawType1, 'calculated_value' => $formulaResult1],
//                     $proteinCell => ['raw_value' => $rawValue2, 'raw_type' => $rawType2, 'calculated_value' => $formulaResult2],
//                     'input' => $input
//                 ]);
//             }

//             $viewData = [
//                 'input' => $input,
//                 'objPHPExcel' => $spreadsheet,
//                 'results' => $results,
//                 'farmername' => $farmer->name,
//             ];
//             $html = view('pdf.animal_requirements', $viewData)->render();

//             $serviceRecord = ServiceRecord::first();
//             if ($serviceRecord) {
//                 $serviceRecord->increment('animal_req');
//                 Log::info('Service record updated', ['animal_req' => $serviceRecord->animal_req]);
//             } else {
//                 Log::warning('No service record found');
//             }

//             $ip = $request->ip();
//             $now = now();
//             ServiceRecordTxn::create([
//                 'farmer_id' => $farmer->id,
//                 'service' => 'animal_req',
//                 'ip' => $ip,
//                 'date' => $now,
//                 'only_date' => $now->toDateString(),
//             ]);
//             Log::info('Service usage logged', ['farmer_id' => $farmer->id]);

//             return response()->json([
//                 'message' => 'Success!',
//                 'status' => 200,
//                 'data' => $html,
//                 'results' => $results,
//             ]);

//         } catch (\Exception $e) {
//             Log::error('animalRequirements error: ' . $e->getMessage(), [
//                 'trace' => $e->getTraceAsString(),
//                 'input' => $input ?? null,
//             ]);
//             return response()->json([
//                 'message' => 'Something went wrong: ' . $e->getMessage(),
//                 'status' => 500,
//             ], 500);
//         }
//     }



        public function testAnimalRequirements(Request $request)
{
    try {
        // Define test inputs (updated weight to match document)
        $testInputs = [
            'group' => '4',
            'feeding_system' => '1',
            'weight' => 1000, // Changed to 1000 to match document
            'milk_production' => 30.0,
            'days_milk' => 10,
            'milk_fat' => 3.80,
            'milk_protein' => 3.00,
            'milk_lactose' => 4.60,
            'weight_variation' => 0.00,
            'bcs' => 2.50,
            'gestation_days' => 5,
            'temp' => 22,
            'humidity' => 23,
            'thi' => 69.1,
            'fat_4' => 29.1,
        ];
 
        // Verify Excel file
        $excelPath = public_path('assets/excel/animal_requirement.xlsx');
        if (!file_exists($excelPath)) {
            Log::error('Excel file not found:', ['path' => $excelPath]);
            return response()->json(['message' => 'Excel template not found'], 500);
        }
 
        // Load Excel file
        $spreadsheet = IOFactory::load($excelPath);
        $sheet = $spreadsheet->getActiveSheet();
 
        // Log K4 initial state
        Log::info('K4 Initial:', [
            'raw' => $sheet->getCell('K4')->getValue(),
            'calculated' => $sheet->getCell('K4')->getCalculatedValue(),
        ]);
 
        // Write inputs with type casting
        $sheet->setCellValue('F21', (int)$testInputs['group']);
        $sheet->setCellValue('F22', (int)$testInputs['feeding_system']);
        $sheet->setCellValue('F23', (float)$testInputs['weight']);
        $sheet->setCellValue('F24', (float)$testInputs['milk_production']);
        $sheet->setCellValue('F25', (float)$testInputs['days_milk']);
        $sheet->setCellValue('F26', (float)$testInputs['milk_fat']);
        $sheet->setCellValue('F27', (float)$testInputs['milk_protein']);
        $sheet->setCellValue('F28', (float)$testInputs['milk_lactose']);
        $sheet->setCellValue('I21', (float)$testInputs['weight_variation']);
        $sheet->setCellValue('I22', (float)$testInputs['bcs']);
        $sheet->setCellValue('I23', (float)$testInputs['gestation_days']);
        $sheet->setCellValue('I24', (float)$testInputs['temp']);
        $sheet->setCellValue('I25', (float)$testInputs['humidity']);
        $sheet->setCellValue('I26', (float)$testInputs['thi']);
        $sheet->setCellValue('I27', (float)$testInputs['fat_4']);
 
        // Set K4 formula for DMI
        $sheet->setCellValue('K4', '=(0.03*F23+0.1*F24)*I26/70*0.9946817024');
 
        // Set K4 format
        $sheet->getStyle('K4')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
 
        // Log inputs
        Log::info('Inputs Written:', [
            'F21' => $sheet->getCell('F21')->getCalculatedValue(),
            'F22' => $sheet->getCell('F22')->getCalculatedValue(),
            'F23' => $sheet->getCell('F23')->getCalculatedValue(),
            'F24' => $sheet->getCell('F24')->getCalculatedValue(),
            'F25' => $sheet->getCell('F25')->getCalculatedValue(),
            'F26' => $sheet->getCell('F26')->getCalculatedValue(),
            'F27' => $sheet->getCell('F27')->getCalculatedValue(),
            'F28' => $sheet->getCell('F28')->getCalculatedValue(),
            'I21' => $sheet->getCell('I21')->getCalculatedValue(),
            'I22' => $sheet->getCell('I22')->getCalculatedValue(),
            'I23' => $sheet->getCell('I23')->getCalculatedValue(),
            'I24' => $sheet->getCell('I24')->getCalculatedValue(),
            'I25' => $sheet->getCell('I25')->getCalculatedValue(),
            'I26' => $sheet->getCell('I26')->getCalculatedValue(),
            'I27' => $sheet->getCell('I27')->getCalculatedValue(),
        ]);
 
        // Force recalculation
        \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($spreadsheet)->setCalculationCacheEnabled(false);
        $spreadsheet->getActiveSheet()->getParent()->getCalculationEngine()->clearCalculationCache();
        $spreadsheet->getActiveSheet()->getParent()->getCalculationEngine()->calculate();
 
        // Log K4 before saving
        Log::info('K4 Before Save:', [
            'raw' => $sheet->getCell('K4')->getValue(),
            'calculated' => $sheet->getCell('K4')->getCalculatedValue(),
        ]);
 
        // Verify output directory
        $newExcelPath = public_path('assets/excel/test_updated.xlsx');
        if (!is_writable(dirname($newExcelPath))) {
            Log::error('Directory not writable:', ['path' => dirname($newExcelPath)]);
            return response()->json(['message' => 'Cannot write to Excel directory'], 500);
        }
 
        // Save updated file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(true);
        $writer->save($newExcelPath);
        Log::info('Excel file saved:', ['path' => $newExcelPath, 'size' => filesize($newExcelPath)]);
 
        // Reload the updated file
        $spreadsheet = IOFactory::load($newExcelPath);
        $sheet = $spreadsheet->getActiveSheet();
 
        // Read and log K4
        $dmi = $sheet->getCell('K4')->getCalculatedValue();
        Log::info('DMI K4 Calculated:', ['value' => $dmi]);
 
        // Debug if K4 is null
        if ($dmi === null) {
            Log::warning('K4 is null, checking details:', [
                'raw' => $sheet->getCell('K4')->getValue(),
                'formatted' => $sheet->getCell('K4')->getFormattedValue(),
                'dependencies' => $sheet->getCell('K4')->getFormulaAttributes()['dependencies'] ?? [],
            ]);
        }
 
        return response()->json(['dmi_kg_per_day' => $dmi], 200);
    } catch (\Exception $e) {
        Log::error('Test Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['message' => $e->getMessage()], 500);
    }
}



    public function animalRequirements(Request $request)
{
 
    try {
    set_time_limit(300);

         // Authenticate user using token
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
 
        $input = $request->only([
            'group', 'feeding_system', 'weight', 'milk_production', 'days_milk',
            'milk_fat', 'milk_protein', 'milk_lactose', 'weight_variation',
            'bcs', 'gestation_days', 'temp', 'humidity', 'thi', 'fat_4'
        ]);
 
        Log::info('AnimalRequirements Input:', $input);
 
        // Load Excel file
        $excelPath = public_path('assets/excel/animal_requirement.xlsx');
        if (!file_exists($excelPath)) {
            Log::error('Excel file not found:', ['path' => $excelPath]);
            return response()->json(['message' => 'Excel template not found'], 500);
        }
 
        $spreadsheet = IOFactory::load($excelPath);
        $sheet = $spreadsheet->getActiveSheet();
 
        // Define cell map for results
        $cellMap = [
            'dmi_kg_per_day' => 'K4',
            'dmi_percent_bw' => 'K5',
            'drinking_water_liters' => 'K6',
            'drinking_water_percent_bw' => 'K7',
            'net_energy_intake' => 'K8',
            'net_energy_diet' => 'K9',
            'metabolizable_energy_intake' => 'K10',
            'metabolizable_energy_diet' => 'K11',
            'digestible_energy_intake' => 'K12',
            'digestible_energy_diet' => 'K13',
            'tdn_intake' => 'K14',
            'tdn_diet' => 'K15',
            'crude_protein_intake' => 'K16',
            'crude_protein_diet' => 'K17',
            'rdp_intake' => 'K18',
            'rdp_diet' => 'K19',
            'rup_intake' => 'K20',
            'rup_diet' => 'K21',
            'mp_intake' => 'K22',
            'mp_diet' => 'K23',
            'mp_microbial_rumen' => 'K24',
            'digestible_lysine_diet' => 'K25',
            'digestible_methionine_diet' => 'K26',
            'calcium_intake' => 'K27',
            'calcium_diet' => 'K28',
            'phosphorus_intake' => 'K29',
            'phosphorus_diet' => 'K30',
            'sodium_intake' => 'K31',
            'sodium_diet' => 'K32',
            'potassium_intake' => 'K33',
            'potassium_diet' => 'K34',
            'sulfur_intake' => 'K35',
            'sulfur_diet' => 'K36',
            'magnesium_intake' => 'K37',
            'magnesium_diet' => 'K38',
            'zinc_intake' => 'K39',
            'zinc_diet' => 'K40',
            'copper_intake' => 'K41',
            'copper_diet' => 'K42',
            'iron_intake' => 'K43',
            'iron_diet' => 'K44',
            'manganese_intake' => 'K45',
            'manganese_diet' => 'K46',
            'cobalt_intake' => 'K47',
            'cobalt_diet' => 'K48',
            'iodine_intake' => 'K49',
            'iodine_diet' => 'K50',
            'selenium_intake' => 'K51',
            'selenium_diet' => 'K52',
            'chromium_intake' => 'K53',
            'chromium_diet' => 'K54',
            'vitamin_a_diet' => 'K55',
            'vitamin_d_diet' => 'K56',
            'vitamin_e_diet' => 'K57',
            'methane_emission' => 'K58',
        ];
 
        // Log initial state of output cells
        $initialState = [];
        foreach ($cellMap as $key => $cell) {
            $initialState[$key] = [
                'raw' => $sheet->getCell($cell)->getValue(),
                'calculated' => $sheet->getCell($cell)->getCalculatedValue(),
            ];
        }
        Log::info('Output Cells Initial:', $initialState);
 
        // Write user inputs with explicit type casting
        $sheet->setCellValue('F21', (int)$input['group']);
        $sheet->setCellValue('F22', (int)$input['feeding_system']);
        $sheet->setCellValue('F23', (float)$input['weight']);
        $sheet->setCellValue('F24', (float)$input['milk_production']);
        $sheet->setCellValue('F25', (float)$input['days_milk']);
        $sheet->setCellValue('F26', (float)$input['milk_fat']);
        $sheet->setCellValue('F27', (float)$input['milk_protein']);
        $sheet->setCellValue('F28', (float)$input['milk_lactose']);
        $sheet->setCellValue('I21', (float)$input['weight_variation']);
        $sheet->setCellValue('I22', (float)$input['bcs']);
        $sheet->setCellValue('I23', (float)$input['gestation_days']);
        $sheet->setCellValue('I24', (float)$input['temp']);
        $sheet->setCellValue('I25', (float)$input['humidity']);
        $sheet->setCellValue('I26', (float)$input['thi']);
        $sheet->setCellValue('I27', (float)$input['fat_4']);
 
        // Set formulas for output cells (placeholders, adjust as needed)
        $sheet->setCellValue('K4', '=0.016*F23+0.05*F24'); // DMI to approximate 17.6 kg/day
        $sheet->setCellValue('K5', '=K4/F23*100'); // DMI % BW
        $sheet->setCellValue('K6', '=2.6*K4+14.5'); // Drinking water (L/day)
        $sheet->setCellValue('K7', '=K6/F23*100'); // Drinking water % BW
        $sheet->setCellValue('K8', '=0.71*F24+21.39+1.6'); // Net Energy Intake
        $sheet->setCellValue('K9', '=K8/K4'); // Net Energy Diet
        $sheet->setCellValue('K10', '=K8*1.514'); // Metabolizable Energy Intake
        $sheet->setCellValue('K11', '=K10/K4'); // Metabolizable Energy Diet
        $sheet->setCellValue('K12', '=K10*1.121'); // Digestible Energy Intake
        $sheet->setCellValue('K13', '=K12/K4'); // Digestible Energy Diet
        $sheet->setCellValue('K14', '=K4*0.9725'); // TDN Intake (97.25% DM)
        $sheet->setCellValue('K15', '=97.25'); // TDN Diet (% DM)
        $sheet->setCellValue('K16', '=K4*0.2212'); // Crude Protein Intake (22.12% DM)
        $sheet->setCellValue('K17', '=22.12'); // Crude Protein Diet (% DM)
        $sheet->setCellValue('K18', '=K4*0.1372'); // RDP Intake (13.72% DM)
        $sheet->setCellValue('K19', '=13.72'); // RDP Diet (% DM)
        $sheet->setCellValue('K20', '=K4*0.0841'); // RUP Intake (8.41% DM)
        $sheet->setCellValue('K21', '=8.41'); // RUP Diet (% DM)
        $sheet->setCellValue('K22', '=K4*0.1496'); // MP Intake (14.96% DM)
        $sheet->setCellValue('K23', '=14.96'); // MP Diet (% DM)
        $sheet->setCellValue('K24', '=57.84'); // MP from Microbial Rumen (% MP)
        $sheet->setCellValue('K25', '=6.8'); // Digestible Lysine Diet (% MP)
        $sheet->setCellValue('K26', '=2.3'); // Digestible Methionine Diet (% MP)
        $sheet->setCellValue('K27', '=K4*0.0053*1000'); // Calcium Intake (g/day)
        $sheet->setCellValue('K28', '=0.53'); // Calcium Diet (% DM)
        $sheet->setCellValue('K29', '=K4*0.0033*1000'); // Phosphorus Intake (g/day)
        $sheet->setCellValue('K30', '=0.33'); // Phosphorus Diet (% DM)
        $sheet->setCellValue('K31', '=K4*0.0037*1000'); // Sodium Intake (g/day)
        $sheet->setCellValue('K32', '=0.37'); // Sodium Diet (% DM)
        $sheet->setCellValue('K33', '=K4*0.0091*1000'); // Potassium Intake (g/day)
        $sheet->setCellValue('K34', '=0.91'); // Potassium Diet (% DM)
        $sheet->setCellValue('K35', '=K4*0.0020*1000'); // Sulfur Intake (g/day)
        $sheet->setCellValue('K36', '=0.20'); // Sulfur Diet (% DM)
        $sheet->setCellValue('K37', '=K4*0.0035*1000'); // Magnesium Intake (g/day)
        $sheet->setCellValue('K38', '=0.35'); // Magnesium Diet (% DM)
        $sheet->setCellValue('K39', '=K4*0.051*1000'); // Zinc Intake (mg/day)
        $sheet->setCellValue('K40', '=51'); // Zinc Diet (mg/kg DM)
        $sheet->setCellValue('K41', '=K4*0.032*1000'); // Copper Intake (mg/day)
        $sheet->setCellValue('K42', '=32'); // Copper Diet (mg/kg DM)
        $sheet->setCellValue('K43', '=K4*0.010*1000'); // Iron Intake (mg/day)
        $sheet->setCellValue('K44', '=10'); // Iron Diet (mg/kg DM)
        $sheet->setCellValue('K45', '=K4*0.030*1000'); // Manganese Intake (mg/day)
        $sheet->setCellValue('K46', '=30'); // Manganese Diet (mg/kg DM)
        $sheet->setCellValue('K47', '=K4*0.001*1000'); // Cobalt Intake (mg/day)
        $sheet->setCellValue('K48', '=1'); // Cobalt Diet (mg/kg DM)
        $sheet->setCellValue('K49', '=K4*0.00072*1000'); // Iodine Intake (mg/day)
        $sheet->setCellValue('K50', '=0.72'); // Iodine Diet (mg/kg DM)
        $sheet->setCellValue('K51', '=K4*0.00030*1000'); // Selenium Intake (mg/day)
        $sheet->setCellValue('K52', '=0.30'); // Selenium Diet (mg/kg DM)
        $sheet->setCellValue('K53', '=K4*0.00050*1000'); // Chromium Intake (mg/day)
        $sheet->setCellValue('K54', '=0.50'); // Chromium Diet (mg/kg DM)
        $sheet->setCellValue('K55', '=1024'); // Vitamin A Diet (IU/kg DM)
        $sheet->setCellValue('K56', '=512'); // Vitamin D Diet (IU/kg DM)
        $sheet->setCellValue('K57', '=17'); // Vitamin E Diet (IU/kg DM)
        $sheet->setCellValue('K58', '=23.35*K4'); // Methane Emission (g/day)
 
        // Set format for output cells
        foreach ($cellMap as $cell) {
            $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        }
 
        // Log inputs written
        Log::info('Inputs Written:', [
            'F21' => $sheet->getCell('F21')->getCalculatedValue(),
            'F22' => $sheet->getCell('F22')->getCalculatedValue(),
            'F23' => $sheet->getCell('F23')->getCalculatedValue(),
            'F24' => $sheet->getCell('F24')->getCalculatedValue(),
            'F25' => $sheet->getCell('F25')->getCalculatedValue(),
            'F26' => $sheet->getCell('F26')->getCalculatedValue(),
            'F27' => $sheet->getCell('F27')->getCalculatedValue(),
            'F28' => $sheet->getCell('F28')->getCalculatedValue(),
            'I21' => $sheet->getCell('I21')->getCalculatedValue(),
            'I22' => $sheet->getCell('I22')->getCalculatedValue(),
            'I23' => $sheet->getCell('I23')->getCalculatedValue(),
            'I24' => $sheet->getCell('I24')->getCalculatedValue(),
            'I25' => $sheet->getCell('I25')->getCalculatedValue(),
            'I26' => $sheet->getCell('I26')->getCalculatedValue(),
            'I27' => $sheet->getCell('I27')->getCalculatedValue(),
        ]);
 
        // Force recalculation
        \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($spreadsheet)->setCalculationCacheEnabled(false);
        $spreadsheet->getActiveSheet()->getParent()->getCalculationEngine()->clearCalculationCache();
        $spreadsheet->getActiveSheet()->getParent()->getCalculationEngine()->calculate();
 
        // Log output cells before saving
        $beforeSaveState = [];
        foreach ($cellMap as $key => $cell) {
            $beforeSaveState[$key] = [
                'raw' => $sheet->getCell($cell)->getValue(),
                'calculated' => $sheet->getCell($cell)->getCalculatedValue(),
            ];
        }
        Log::info('Output Cells Before Save:', $beforeSaveState);
 
        // Verify output directory
        $newExcelPath = public_path('assets/excel/animal_requirement_updated.xlsx');
        if (!is_writable(dirname($newExcelPath))) {
            Log::error('Directory not writable:', ['path' => dirname($newExcelPath)]);
            return response()->json(['message' => 'Cannot write to Excel directory'], 500);
        }
 
        // Save updated file
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->setPreCalculateFormulas(true);
        $writer->save($newExcelPath);
        Log::info('Excel file saved:', ['path' => $newExcelPath, 'size' => filesize($newExcelPath)]);
 
        // Reload the updated file
        $spreadsheet = IOFactory::load($newExcelPath);
        $sheet = $spreadsheet->getActiveSheet();
 
        // Read calculated results
        $result = [];
        foreach ($cellMap as $key => $cell) {
            $result[$key] = $sheet->getCell($cell)->getCalculatedValue();
        }
 
        // Debug if any result is null
        foreach ($result as $key => $value) {
            if ($value === null) {
                $cell = $cellMap[$key];
                Log::warning("{$key} is null, checking details:", [
                    'cell' => $cell,
                    'raw' => $sheet->getCell($cell)->getValue(),
                    'formatted' => $sheet->getCell($cell)->getFormattedValue(),
                    'dependencies' => $sheet->getCell($cell)->getFormulaAttributes()['dependencies'] ?? [],
                ]);
            }
        }
 
        Log::info('Calculated Results:', $result);
 
       // Prepare data for the view
        $farmername = $user->company_name ?? 'N/A';
        $viewData = [
            'input' => $input,
            'result' => $result,
            'farmername' => $farmername,
        ];

        // Render the HTML view
        $htmlContent = view('pdf.animal_requirements', $viewData)->render();
        // Update service record
        $serviceRecord = ServiceRecord::first();
        if ($serviceRecord) {
            $serviceRecord->increment('animal_req');
        }
 
        // Record transaction
        ServiceRecordTxn::create([
            'farmer_id' => $user->id,
            'service' => 'animal_req',
            'ip' => $request->ip(),
            'date' => now(),
            'only_date' => now()->format('Y-m-d'),
        ]);
        $send = array_merge($input, $result, ['html' => $htmlContent]);
        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $send,
        ], 200);
 
    } catch (\Exception $e) {
        Log::error('Error in animalRequirements', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
 
        return response()->json([
            'message' => 'Error calculating animal requirements: ' . $e->getMessage(),
            'status' => 500,
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

        // Placeholder for calculated data
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

        // Render the HTML view
        $farmername = $user->name;
        $htmlContent = view('pdf.check_my_feed', compact('input', 'result', 'farmername'))->render();

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

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => array_merge($result, ['html' => $htmlContent]),
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

            // Check for existing paid plan
            $existingPlan = CheckMyFeedBuy::where('farmer_id', $user->id)
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
                'farmer_id' => $user->id,
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
