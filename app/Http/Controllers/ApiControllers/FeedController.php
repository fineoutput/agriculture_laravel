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
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

            // Generate HTML
            // $html = View::make('pdf.dmi', compact('input', 'result'))->render();

            // // Add HTML to response data
            // $result['html'] = $html;

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

            // Decode JSON inputs (default to empty arrays if null)
            $proteinData = $request->ProteinData ? json_decode($request->ProteinData, true) : [];
            $energyData = $request->EnergyData ? json_decode($request->EnergyData, true) : [];
            $productData = $request->ProductData ? json_decode($request->ProductData, true) : [];
            $medicineData = $request->MedicineData ? json_decode($request->MedicineData, true) : [];

            // Initialize nutritional metrics
            $cp = $ee = $cf = $tdn = $me = $ca = $p = $adf = $ndf = $nel = $rudp = $endf = $value = 0;

            // Process ProteinData
            foreach ($proteinData as $item) {
                if (!empty($item[3])) {
                    $cp += isset($item[4]) ? $item[4] * $item[3] / 1000 : 0;
                    $ee += isset($item[5]) ? $item[5] * $item[3] / 1000 : 0;
                    $cf += isset($item[6]) ? $item[6] * $item[3] / 1000 : 0;
                    $tdn += isset($item[7]) ? $item[7] * $item[3] / 1000 : 0;
                    $me += isset($item[8]) ? $item[8] * $item[3] / 1000 : 0;
                    $ca += isset($item[9]) ? $item[9] * $item[3] / 1000 : 0;
                    $p += isset($item[10]) ? $item[10] * $item[3] / 1000 : 0;
                    $adf += isset($item[11]) ? $item[11] * $item[3] / 1000 : 0;
                    $ndf += isset($item[12]) ? $item[12] * $item[3] / 1000 : 0;
                    $nel += isset($item[13]) ? $item[13] * $item[3] / 1000 : 0;
                    $rudp += isset($item[14]) ? $item[14] * $item[3] / 1000 : 0;
                    $endf += isset($item[15]) ? $item[15] * $item[3] / 1000 : 0;
                    $value += isset($item[2]) && isset($item[3]) ? $item[2] * $item[3] : 0;
                }
            }

            // Process EnergyData
            foreach ($energyData as $item) {
                if (!empty($item[3])) {
                    $cp += isset($item[4]) ? $item[4] * $item[3] / 1000 : 0;
                    $ee += isset($item[5]) ? $item[5] * $item[3] / 1000 : 0;
                    $cf += isset($item[6]) ? $item[6] * $item[3] / 1000 : 0;
                    $tdn += isset($item[7]) ? $item[7] * $item[3] / 1000 : 0;
                    $me += isset($item[8]) ? $item[8] * $item[3] / 1000 : 0;
                    $ca += isset($item[9]) ? $item[9] * $item[3] / 1000 : 0;
                    $p += isset($item[10]) ? $item[10] * $item[3] / 1000 : 0;
                    $adf += isset($item[11]) ? $item[11] * $item[3] / 1000 : 0;
                    $ndf += isset($item[12]) ? $item[12] * $item[3] / 1000 : 0;
                    $nel += isset($item[13]) ? $item[13] * $item[3] / 1000 : 0;
                    $rudp += isset($item[14]) ? $item[14] * $item[3] / 1000 : 0;
                    $endf += isset($item[15]) ? $item[15] * $item[3] / 1000 : 0;
                    $value += isset($item[2]) && isset($item[3]) ? $item[2] * $item[3] : 0;
                }
            }

            // Process ProductData
            foreach ($productData as $item) {
                if (!empty($item[3])) {
                    $cp += isset($item[4]) ? $item[4] * $item[3] / 1000 : 0;
                    $ee += isset($item[5]) ? $item[5] * $item[3] / 1000 : 0;
                    $cf += isset($item[6]) ? $item[6] * $item[3] / 1000 : 0;
                    $tdn += isset($item[7]) ? $item[7] * $item[3] / 1000 : 0;
                    $me += isset($item[8]) ? $item[8] * $item[3] / 1000 : 0;
                    $ca += isset($item[9]) ? $item[9] * $item[3] / 1000 : 0;
                    $p += isset($item[10]) ? $item[10] * $item[3] / 1000 : 0;
                    $adf += isset($item[11]) ? $item[11] * $item[3] / 1000 : 0;
                    $ndf += isset($item[12]) ? $item[12] * $item[3] / 1000 : 0;
                    $nel += isset($item[13]) ? $item[13] * $item[3] / 1000 : 0;
                    $rudp += isset($item[14]) ? $item[14] * $item[3] / 1000 : 0;
                    $endf += isset($item[15]) ? $item[15] * $item[3] / 1000 : 0;
                    $value += isset($item[2]) && isset($item[3]) ? $item[2] * $item[3] : 0;
                }
            }

            // Process MedicineData
            foreach ($medicineData as $item) {
                if (!empty($item[3])) {
                    $cp += isset($item[4]) ? $item[4] * $item[3] / 1000 : 0;
                    $ee += isset($item[5]) ? $item[5] * $item[3] / 1000 : 0;
                    $cf += isset($item[6]) ? $item[6] * $item[3] / 1000 : 0;
                    $tdn += isset($item[7]) ? $item[7] * $item[3] / 1000 : 0;
                    $me += isset($item[8]) ? $item[8] * $item[3] / 1000 : 0;
                    $ca += isset($item[9]) ? $item[9] * $item[3] / 1000 : 0;
                    $p += isset($item[10]) ? $item[10] * $item[3] / 1000 : 0;
                    $adf += isset($item[11]) ? $item[11] * $item[3] / 1000 : 0;
                    $ndf += isset($item[12]) ? $item[12] * $item[3] / 1000 : 0;
                    $nel += isset($item[13]) ? $item[13] * $item[3] / 1000 : 0;
                    $rudp += isset($item[14]) ? $item[14] * $item[3] / 1000 : 0;
                    $endf += isset($item[15]) ? $item[15] * $item[3] / 1000 : 0;
                    $value += isset($item[2]) && isset($item[3]) ? $item[2] * $item[3] : 0;
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

            // Prepare result for HTML
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

            // Generate HTML
            $html = View::make('pdf.feed', compact('result'))->render();

            // Prepare response data
            $send = [
                'fresh' => $fresh,
                'dmb' => $dmb,
                'row_ton' => round($value, 2),
                'row_qtl' => round($value / 10, 2),
                'html' => $html,
            ];

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

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $send,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in feedCalculator', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
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

            // Process Excel file
            // $inputFileName = public_path('assets/excel/animal_requirement.xlsx');
            // $outputFileName = public_path('assets/excel/animal_requirement.xls');

            // try {
            //     $spreadsheet = IOFactory::load($inputFileName);
            //     $worksheet = $spreadsheet->getActiveSheet();


            //     $worksheet->setCellValue('F21', $input['group']);
            //     $worksheet->setCellValue('F22', $input['feeding_system']);
            //     $worksheet->setCellValue('F23', $input['weight']);
            //     $worksheet->setCellValue('F24', $input['milk_production']);
            //     $worksheet->setCellValue('F25', $input['days_milk']);
            //     $worksheet->setCellValue('F26', $input['milk_fat']);
            //     $worksheet->setCellValue('F27', $input['milk_protein']);
            //     $worksheet->setCellValue('F28', $input['milk_lactose']);
            //     $worksheet->setCellValue('I21', $input['weight_variation']);
            //     $worksheet->setCellValue('I22', $input['bcs']);
            //     $worksheet->setCellValue('I23', $input['gestation_days']);
            //     $worksheet->setCellValue('I24', $input['temp']);
            //     $worksheet->setCellValue('I25', $input['humidity']);
            //     $worksheet->setCellValue('I26', $input['thi']);
            //     $worksheet->setCellValue('I27', $input['fat_4']);


            //     $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            //     $writer->setPreCalculateFormulas(true);
            //     $writer->save($outputFileName);


            //     $spreadsheet = IOFactory::load($outputFileName);
            //     Log::info('Excel file processed', [
            //         'input_file' => $inputFileName,
            //         'output_file' => $outputFileName,
            //     ]);

            // } catch (\Exception $e) {
            //     Log::error('Error processing Excel file', [
            //         'error' => $e->getMessage(),
            //         'file' => $inputFileName,
            //     ]);
            //     return response()->json([
            //         'message' => 'Error processing Excel file: ' . $e->getMessage(),
            //         'status' => 201,
            //     ], 500);
            // }


            // $html = View::make('pdf.animal_requirements', compact('input', 'spreadsheet'))->render();

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

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                // 'data' => $html,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in animalRequirements', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
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
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('CheckMyFeed auth attempt', [
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
                'live_weight' => 'nullable|numeric',
                'pregnancy' => 'nullable|numeric',
                'milk_yield_volume' => 'nullable|numeric',
                'milk_yield_fat' => 'nullable|numeric',
                'milk_yield_protein' => 'nullable|numeric',
                'live_weight_gain' => 'nullable|numeric',
                'milk_return' => 'nullable|numeric',
                'material' => 'required|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Extract and decode inputs
            $material = json_decode($request->material);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'message' => 'Invalid material JSON format',
                    'status' => 201,
                ], 422);
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

            // Process Excel file
            $inputFileName = public_path('assets/excel/check_my_feed.xlsm');
            $outputFileName = public_path('assets/excel/check_my_feed.xlsm');

            try {
                // Load the Excel file
                $spreadsheet = IOFactory::load($inputFileName);

                // Set values in Worksheet 1 (index 1)
                $spreadsheet->setActiveSheetIndex(1)
                    ->setCellValue('B13', $input['lactation'])
                    ->setCellValue('C4', $input['live_weight'])
                    ->setCellValue('C5', $input['pregnancy'])
                    ->setCellValue('C6', $input['milk_yield_volume'])
                    ->setCellValue('C7', $input['milk_yield_fat'])
                    ->setCellValue('C8', $input['milk_yield_protein'])
                    ->setCellValue('C9', $input['live_weight_gain']);

                // Set value in Worksheet 3 (index 3)
                $spreadsheet->setActiveSheetIndex(3)
                    ->setCellValue('D12', $input['milk_return']);

                // Set material values in Worksheet 2 (index 2) and Worksheet 4 (index 4)
                $freshRow = 4; // Starting row for fresh quantities (D4)
                $priceRow = 7; // Starting row for prices (C7)
                foreach ($material as $mat) {
                    $freshValue = $mat->value ? ($mat->fresh ?? 0) : 0;
                    $priceValue = $mat->value ? ($mat->price ?? 0) : 0;
                    $spreadsheet->setActiveSheetIndex(2)->setCellValue("D{$freshRow}", $freshValue);
                    $spreadsheet->setActiveSheetIndex(4)->setCellValue("C{$priceRow}", $priceValue);
                    $freshRow++;
                    $priceRow++;
                }

                // Save as .xlsm with formulas recalculated
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx'); // Xlsx for .xlsm
                $writer->setPreCalculateFormulas(true);
                $writer->save($outputFileName);

                // Reload the saved .xlsm file
                $spreadsheet = IOFactory::load($outputFileName);
                Log::info('Excel file processed', [
                    'input_file' => $inputFileName,
                    'output_file' => $outputFileName,
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing Excel file', [
                    'error' => $e->getMessage(),
                    'file' => $inputFileName,
                ]);
                return response()->json([
                    'message' => 'Error processing Excel file: ' . $e->getMessage(),
                    'status' => 201,
                ], 500);
            }

            // Generate HTML
            $farmername = $user->name;
            $html = View::make('pdf.check_my_feed', compact('input', 'spreadsheet', 'farmername'))->render();

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
                'data' => $html,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in checkMyFeed', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
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
            // Fetch expert doctors
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
