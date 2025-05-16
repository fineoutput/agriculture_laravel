<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Doctor;
use App\Models\ExpertiseCategory;
use App\Models\Cart;
use App\Models\Vendor;
use App\Models\ServiceRecord;
use App\Models\DoctorRequest;
use App\Models\Product;
use App\Models\State;
use App\Models\ServiceRecordTxn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;
class ToolsController extends Controller
{
    protected function createPagination($currentPage, $totalPages)
    {
        $pagination = [];
        $currentPage = (int) $currentPage;

        if ($totalPages <= 5) {
            for ($i = 1; $i <= $totalPages; $i++) {
                $pagination[] = [
                    'page' => $i,
                    'is_active' => $i === $currentPage,
                ];
            }
        } else {
            $start = max(1, $currentPage - 2);
            $end = min($totalPages, $currentPage + 2);

            if ($start > 1) {
                $pagination[] = ['page' => 1, 'is_active' => false];
                if ($start > 2) {
                    $pagination[] = ['page' => '...', 'is_active' => false];
                }
            }

            for ($i = $start; $i <= $end; $i++) {
                $pagination[] = [
                    'page' => $i,
                    'is_active' => $i === $currentPage,
                ];
            }

            if ($end < $totalPages) {
                if ($end < $totalPages - 1) {
                    $pagination[] = ['page' => '...', 'is_active' => false];
                }
                $pagination[] = ['page' => $totalPages, 'is_active' => false];
            }
        }

        return $pagination;
    }
    public function silageMaking(Request $request)
    {
        Log::info('silageMaking request', [
            'number_of_cows' => $request->input('number_of_cows'),
            'feeding' => $request->input('feeding'),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        try {
            // Check if any required data is provided
            if (!$request->hasAny(['number_of_cows', 'feeding', 'total_feeding_days', 'density', 'breadth', 'height', 'number_of_pits'])) {
                Log::warning('silageMaking: No data provided', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate authentication header
            $token = $request->header('Authentication');
            $validator = Validator::make(['Authentication' => $token], [
                'Authentication' => 'required|string',
            ], [
                'Authentication.required' => 'Authentication token is required',
            ]);

            if ($validator->fails()) {
                Log::warning('silageMaking: Validation failed for authentication', [
                    'errors' => $validator->errors(),
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::info('silageMaking: Auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? $farmer->is_active : null,
                'authentication_header' => $token,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('silageMaking: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'number_of_cows' => 'required|numeric|min:1',
                'feeding' => 'required|numeric|min:0',
                'total_feeding_days' => 'required|numeric|min:1',
                'density' => 'required|numeric|min:0.1',
                'breadth' => 'required|numeric|min:0.1',
                'height' => 'required|numeric|min:0.1',
                'number_of_pits' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('silageMaking: Input validation failed', [
                    'errors' => $validator->errors(),
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $number_of_cows = $request->input('number_of_cows');
            $feeding = $request->input('feeding');
            $total_feeding_days = $request->input('total_feeding_days');
            $density = $request->input('density');
            $breadth = $request->input('breadth');
            $height = $request->input('height');
            $number_of_pits = $request->input('number_of_pits');

            // Calculations
            $silage_qty_required = round($number_of_cows * $feeding * $total_feeding_days, 2);
            $pit_vol_required = round($silage_qty_required / $density, 2);
            $length = round($pit_vol_required / ($breadth * $height * $number_of_pits), 2);
            $fodder_required = round(($silage_qty_required * 15 / 10) / 1000, 2);

            $data = [
                'silage_qty_required' => $silage_qty_required,
                'pit_vol_required' => $pit_vol_required,
                'length' => $length,
                'fodder_required' => $fodder_required,
            ];

            // Update service record
            $service_record = ServiceRecord::first();
            if (!$service_record) {
                Log::warning('silageMaking: No service record found in tbl_service_records', [
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Service record not found!',
                    'status' => 201,
                ], 500);
            }

            $service_record->update([
                'silage_making' => ($service_record->silage_making ?? 0) + 1,
            ]);

            // Create transaction
            ServiceRecordTxn::create([
                'farmer_id' => $farmer->id,
                'service' => 'silage_making',
                'ip' => $request->ip(),
                'date' => now(),
                'only_date' => now()->format('Y-m-d'),
            ]);

            Log::info('silageMaking: Success', [
                'farmer_id' => $farmer->id,
                'silage_qty_required' => $silage_qty_required,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('silageMaking: Error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Error calculating silage requirements: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function projectRequirements(Request $request)
    {
        try {
            if (!$request->has('number_of_cows')) {
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // /** @var \App\Models\Farmer $farmer */
            $farmer = auth('farmer')->user();
            Log::info('ProjectRequirements auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? ($farmer->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$farmer || !$farmer->is_active) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'number_of_cows' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $number_of_cows = $request->input('number_of_cows');
            $excel_path = public_path('excel/25_cows.xlsx');

            if (!file_exists($excel_path)) {
                Log::error('Excel file not found', ['path' => $excel_path]);
                return response()->json([
                    'message' => 'Excel file not found!',
                    'status' => 201,
                ], 500);
            }

            // Load and update Excel file
            $spreadsheet = IOFactory::load($excel_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setCellValue('B9', $number_of_cows);
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($excel_path);

            // Reload to get calculated values
            $spreadsheet = IOFactory::load($excel_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $calculated_value = $worksheet->getCell('C12')->getCalculatedValue();

            // Generate PDF
            $data = [
                'number_of_cows' => $number_of_cows,
                'calculated_value' => $calculated_value,
            ];

            $pdf = Pdf::loadView('pdf.25_cows', $data);
            $pdf_content = $pdf->output();
            $base64_pdf = base64_encode($pdf_content);

            // Update service record
            $service_record = ServiceRecord::first();
            if (!$service_record) {
                Log::warning('No service record found in tbl_service_records');
                return response()->json([
                    'message' => 'Service record not found!',
                    'status' => 201,
                ], 500);
            }

            $service_record->update([
                'pro_req' => ($service_record->pro_req ?? 0) + 1,
            ]);

            // Create transaction
            ServiceRecordTxn::create([
                'farmer_id' => $farmer->id,
                'service' => 'pro_req',
                'ip' => $request->ip(),
                'date' => now(),
                'only_date' => now()->format('Y-m-d'),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $base64_pdf,
                'filename' => 'project_requirements.pdf',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in projectRequirements', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error processing project requirements: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function projectTest(Request $request)
    {
        try {
            // /** @var \App\Models\Farmer $farmer */
            $farmer = auth('farmer')->user();
            Log::info('ProjectTest auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? ($farmer->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$farmer || !$farmer->is_active) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $excel_path = public_path('excel/25_cows.xlsx');

            if (!file_exists($excel_path)) {
                Log::error('Excel file not found', ['path' => $excel_path]);
                return response()->json([
                    'message' => 'Excel file not found!',
                    'status' => 201,
                ], 500);
            }

            // Load and update Excel file
            $spreadsheet = IOFactory::load($excel_path);
            $worksheet = $spreadsheet->getActiveSheet();
            $worksheet->setCellValue('B9', 50); // Hardcoded as in original
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($excel_path);

            // Reload to get calculated values
            $spreadsheet = IOFactory::load($excel_path);
            $worksheet = $spreadsheet->getActiveSheet();

            // Extract cell values
            $data = [
                'number_of_cows' => 50,
                'C12' => number_format($worksheet->getCell('C12')->getCalculatedValue(), 2),
                'C13' => number_format($worksheet->getCell('C13')->getCalculatedValue(), 2),
                'C14' => number_format($worksheet->getCell('C14')->getCalculatedValue(), 2),
                'C15' => round($worksheet->getCell('C15')->getCalculatedValue(), 2),
                'D15' => round($worksheet->getCell('D15')->getCalculatedValue(), 2),
                'E15' => round($worksheet->getCell('E15')->getCalculatedValue(), 2),
                'F15' => round($worksheet->getCell('F15')->getCalculatedValue(), 2),
                'G15' => round($worksheet->getCell('G15')->getCalculatedValue(), 2),
                'H15' => round($worksheet->getCell('H15')->getCalculatedValue(), 2),
                'C16' => round($worksheet->getCell('C16')->getCalculatedValue(), 2),
                'D16' => round($worksheet->getCell('D16')->getCalculatedValue(), 2),
                'E16' => round($worksheet->getCell('E16')->getCalculatedValue(), 2),
                'F16' => round($worksheet->getCell('F16')->getCalculatedValue(), 2),
                'G16' => round($worksheet->getCell('G16')->getCalculatedValue(), 2),
                'H16' => round($worksheet->getCell('H16')->getCalculatedValue(), 2),
                'C17' => round($worksheet->getCell('C17')->getCalculatedValue(), 2),
                'D17' => round($worksheet->getCell('D17')->getCalculatedValue(), 2),
                'E17' => round($worksheet->getCell('E17')->getCalculatedValue(), 2),
                'F17' => round($worksheet->getCell('F17')->getCalculatedValue(), 2),
                'G17' => round($worksheet->getCell('G17')->getCalculatedValue(), 2),
                'H17' => round($worksheet->getCell('H17')->getCalculatedValue(), 2),
                'C18' => round($worksheet->getCell('C18')->getCalculatedValue(), 2),
                'D18' => round($worksheet->getCell('D18')->getCalculatedValue(), 2),
                'E18' => round($worksheet->getCell('E18')->getCalculatedValue(), 2),
                'F18' => round($worksheet->getCell('F18')->getCalculatedValue(), 2),
                'G18' => round($worksheet->getCell('G18')->getCalculatedValue(), 2),
                'H18' => round($worksheet->getCell('H18')->getCalculatedValue(), 2),
                'C19' => round($worksheet->getCell('C19')->getCalculatedValue(), 2),
                'D19' => round($worksheet->getCell('D19')->getCalculatedValue(), 2),
                'E19' => round($worksheet->getCell('E19')->getCalculatedValue(), 2),
                'F19' => round($worksheet->getCell('F19')->getCalculatedValue(), 2),
                'G19' => round($worksheet->getCell('G19')->getCalculatedValue(), 2),
                'H19' => round($worksheet->getCell('H19')->getCalculatedValue(), 2),
                'C22' => round($worksheet->getCell('C22')->getCalculatedValue(), 2),
                'D23' => round($worksheet->getCell('D23')->getCalculatedValue(), 2),
                'E23' => round($worksheet->getCell('E23')->getCalculatedValue(), 2),
                'F23' => round($worksheet->getCell('F23')->getCalculatedValue(), 2),
                'G23' => round($worksheet->getCell('G23')->getCalculatedValue(), 2),
                'H23' => round($worksheet->getCell('H23')->getCalculatedValue(), 2),
                'C24' => round($worksheet->getCell('C24')->getCalculatedValue(), 2),
                'D24' => round($worksheet->getCell('D24')->getCalculatedValue(), 2),
                'E24' => round($worksheet->getCell('E24')->getCalculatedValue(), 2),
                'F24' => round($worksheet->getCell('F24')->getCalculatedValue(), 2),
                'G24' => round($worksheet->getCell('G24')->getCalculatedValue(), 2),
                'H24' => round($worksheet->getCell('H24')->getCalculatedValue(), 2),
                'C25' => round($worksheet->getCell('C25')->getCalculatedValue(), 2),
                'D25' => round($worksheet->getCell('D25')->getCalculatedValue(), 2),
                'E25' => round($worksheet->getCell('E25')->getCalculatedValue(), 2),
                'F25' => round($worksheet->getCell('F25')->getCalculatedValue(), 2),
                'G25' => round($worksheet->getCell('G25')->getCalculatedValue(), 2),
                'H25' => round($worksheet->getCell('H25')->getCalculatedValue(), 2),
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.25_cows', $data);
            $pdf_content = $pdf->output();
            $base64_pdf = base64_encode($pdf_content);

            // Update service record
            $service_record = ServiceRecord::first();
            if (!$service_record) {
                Log::warning('No service record found in tbl_service_records');
                return response()->json([
                    'message' => 'Service record not found!',
                    'status' => 201,
                ], 500);
            }

            $service_record->update([
                'pro_req' => ($service_record->pro_req ?? 0) + 1,
            ]);

            // Create transaction
            ServiceRecordTxn::create([
                'farmer_id' => $farmer->id,
                'service' => 'project_test',
                'ip' => $request->ip(),
                'date' => now(),
                'only_date' => now()->format('Y-m-d'),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $base64_pdf,
                'filename' => 'project_test.pdf',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in projectTest', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error processing project test: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function pregnancyCalculator(Request $request)
    {
        Log::info('pregnancyCalculator request', [
            'breeding_date' => $request->input('breeding_date'),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        try {
            // Check if breeding_date is provided
            if (!$request->has('breeding_date')) {
                Log::warning('pregnancyCalculator: No data provided', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate authentication header
            $token = $request->header('Authentication');
            $validator = Validator::make(['Authentication' => $token], [
                // 'Authentication' => 'required|string',
            ], [
                'Authentication.required' => 'Authentication token is required',
            ]);

            if ($validator->fails()) {
                Log::warning('pregnancyCalculator: Validation failed for authentication', [
                    'errors' => $validator->errors(),
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::info('pregnancyCalculator: Auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? $farmer->is_active : null,
                'authentication_header' => $token,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('pregnancyCalculator: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'breeding_date' => 'required|date_format:d-m-Y',
            ]);

            if ($validator->fails()) {
                Log::warning('pregnancyCalculator: Input validation failed', [
                    'errors' => $validator->errors(),
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $breeding_date = Carbon::parse($request->input('breeding_date'));

            // Calculate dates
            $cycle1 = $breeding_date->copy()->addDays(21);
            $cycle2 = $breeding_date->copy()->addDays(42);
            $cycle3 = $breeding_date->copy()->addDays(63);
            $calving = $breeding_date->copy()->addDays(283);
            $weaning = $breeding_date->copy()->addDays(488);

            $data = [
                'cycle1_date' => $cycle1->format('d/m/Y'),
                'cycle2_date' => $cycle2->format('d/m/Y'),
                'cycle3_date' => $cycle3->format('d/m/Y'),
                'calving_date' => $calving->format('d/m/Y'),
                'weaning_date' => $weaning->format('d/m/Y'),
            ];

            // Update service record
            $service_record = ServiceRecord::first();
            if (!$service_record) {
                Log::warning('pregnancyCalculator: No service record found in tbl_service_records', [
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Service record not found!',
                    'status' => 201,
                ], 500);
            }

            $service_record->update([
                'preg_calculator' => ($service_record->preg_calculator ?? 0) + 1,
            ]);

            // Create transaction
            ServiceRecordTxn::create([
                'farmer_id' => $farmer->id,
                'service' => 'preg_calculator',
                'ip' => $request->ip(),
                'date' => now(),
                'only_date' => now()->format('Y-m-d'),
            ]);

            Log::info('pregnancyCalculator: Success', [
                'farmer_id' => $farmer->id,
                'breeding_date' => $request->input('breeding_date'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('pregnancyCalculator: Error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Error calculating pregnancy dates: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function snfCalculator(Request $request)
    {
        try {
            if (!$request->hasAny(['type', 'fat'])) {
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // /** @var \App\Models\Farmer $farmer *//
            $farmer = auth('farmer')->user();
            Log::info('SNFCalculator auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? ($farmer->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$farmer || !$farmer->is_active) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|in:SNF,CLR',
                'snf' => 'nullable|numeric|min:0',
                'clr' => 'nullable|numeric|min:0',
                'fat' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $type = $request->input('type');
            $snf = $request->input('snf');
            $clr = $request->input('clr');
            $fat = $request->input('fat');

            // Validate input combinations
            if ($type === 'CLR' && is_null($clr)) {
                return response()->json([
                    'message' => 'CLR is required when type is CLR',
                    'status' => 201,
                ], 422);
            }
            if ($type === 'SNF' && is_null($snf)) {
                return response()->json([
                    'message' => 'SNF is required when type is SNF',
                    'status' => 201,
                ], 422);
            }

            // Calculate percentage
            if ($type === 'CLR') {
                $percentage = round(($clr / 4) + (0.21 * $fat) + 0.66, 2);
            } else {
                $percentage = round(($snf - (0.21 * $fat) - 0.66) * 4, 2);
            }

            // Calculate LSP (Lactose, Solid, Protein) for cow and buffalo
            $lsp = [
                ['component' => 'Lactose', 'cow' => round(($type === 'SNF' ? $snf : $percentage) * 0.55, 3), 'buffalo' => round(($type === 'SNF' ? $snf : $percentage) * 0.45, 3)],
                ['component' => 'Solid', 'cow' => round(($type === 'SNF' ? $snf : $percentage) * 0.083, 3), 'buffalo' => round(($type === 'SNF' ? $snf : $percentage) * 0.076, 3)],
                ['component' => 'Protein', 'cow' => round(($type === 'SNF' ? $snf : $percentage) * 0.367, 3), 'buffalo' => round(($type === 'SNF' ? $snf : $percentage) * 0.475, 3)],
            ];

            $data = [
                'percentage' => $percentage,
                'lsp' => $lsp,
            ];

            // Update service record
            $service_record = ServiceRecord::first();
            if (!$service_record) {
                Log::warning('No service record found in tbl_service_records');
                return response()->json([
                    'message' => 'Service record not found!',
                    'status' => 201,
                ], 500);
            }

            $service_record->update([
                'snf_calculator' => ($service_record->snf_calculator ?? 0) + 1,
            ]);

            // Create transaction
            ServiceRecordTxn::create([
                'farmer_id' => $farmer->id,
                'service' => 'snf_calculator',
                'ip' => $request->ip(),
                'date' => now(),
                'only_date' => now()->format('Y-m-d'),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in snfCalculator', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error calculating SNF/CLR: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function allProducts(Request $request)
    {
        Log::info('allProducts request', [
            'is_admin' => $request->input('is_admin'),
            'vendor_id' => $request->input('vendor_id'),
            'search' => $request->input('search'),
            'page_index' => $request->header('Index', 1),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        try {
            // Check if is_admin is provided
            if (!$request->has('is_admin')) {
                Log::warning('allProducts: No data provided', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate authentication header
            $token = $request->header('Authentication');
            $validator = Validator::make(['Authentication' => $token], [
                // 'Authentication' => 'required|string',
            ], [
                'Authentication.required' => 'Authentication token is required',
            ]);

            if ($validator->fails()) {
                Log::warning('allProducts: Validation failed for authentication', [
                    'errors' => $validator->errors(),
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::info('allProducts: Auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? $farmer->is_active : null,
                'authentication_header' => $token,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('allProducts: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'is_admin' => 'required|string',
                'vendor_id' => 'nullable|integer',
                'search' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                Log::warning('allProducts: Input validation failed', [
                    'errors' => $validator->errors(),
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $is_admin = $request->input('is_admin');
            $vendor_id = $request->input('vendor_id');
            $search = $request->input('search');
            $perPage = 50;
            $page_index = (int) $request->header('Index', 1);

            // Build product query
            $query = Product::query()->where('is_active', 1);

            if ($is_admin === 'admin') {
                $query->where('is_admin', 1);
            } else {
                $query->where('is_admin', 0)
                      ->where('is_approved', 1)
                      ->where('added_by', $vendor_id);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_english', 'LIKE', "%{$search}%")
                      ->orWhere('name_hindi', 'LIKE', "%{$search}%")
                      ->orWhere('name_punjabi', 'LIKE', "%{$search}%");
                });
            }

            // Fetch paginated products
            $products = $query->paginate($perPage, ['*'], 'page', $page_index);

            $en_data = [];
            $hi_data = [];
            $pn_data = [];
            $mr_data = [];

            foreach ($products as $pro) {
                $image = [];
                if (!empty($pro->image)) {
                    $imageArray = json_decode($pro->image, true);
                    if (is_array($imageArray) && !empty($imageArray)) {
                        foreach ($imageArray as $img) {
                            $image[] = asset($img);
                        }
                    } else {
                        $image[] = asset($pro->image);
                    }
                }

                $video = !empty($pro->video) ? asset($pro->video) : '';
                $stock = $pro->inventory != 0 ? 'In Stock' : 'Out of Stock';
                $discount = (int) $pro->mrp - (int) $pro->selling_price;
                $percent = $discount > 0 ? round($discount / $pro->mrp * 100) : 0;

                $common_data = [
                    'pro_id' => $pro->id,
                    'image' => $image,
                    'video' => $video,
                    'mrp' => $pro->mrp,
                    'min_qty' => $pro->min_qty ?? 1,
                    'selling_price' => $pro->selling_price,
                    'suffix' => $pro->suffix,
                    'stock' => $stock,
                    'percent' => $percent,
                    'vendor_id' => $pro->added_by,
                    'is_admin' => $pro->is_admin,
                    'offer' => $pro->offer,
                    'product_cod' => $pro->cod,
                    'is_cod' => $farmer->cod,
                ];

                $en_data[] = array_merge($common_data, [
                    'name' => $pro->name_english,
                    'description' => $pro->description_english,
                ]);

                $hi_data[] = array_merge($common_data, [
                    'name' => $pro->name_hindi,
                    'description' => $pro->description_hindi,
                ]);

                $pn_data[] = array_merge($common_data, [
                    'name' => $pro->name_punjabi,
                    'description' => $pro->description_punjabi,
                ]);

                $mr_data[] = array_merge($common_data, [
                    'name' => $pro->name_marathi,
                    'description' => $pro->description_marathi,
                ]);
            }

            Log::info('allProducts: Success', [
                'farmer_id' => $farmer->id,
                'is_admin' => $is_admin,
                'vendor_id' => $vendor_id,
                'search' => $search,
                'total_products' => $products->total(),
                'page' => $page_index,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => [
                    'en' => $en_data,
                    'hi' => $hi_data,
                    'pn' => $pn_data,
                    'mr' => $mr_data,
                ],
                'is_cod' => $farmer->cod,
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ],
                'last' => $products->lastPage(),
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('allProducts: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'is_admin' => $request->input('is_admin'),
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('allProducts: Error', [
                'farmer_id' => $farmer->id ?? null,
                'is_admin' => $request->input('is_admin'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving products: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function calculateDistance(Request $request)
    {
        try {
            if (!$request->has(['lat1', 'lon1', 'lat2', 'lon2'])) {
                Log::warning('CalculateDistance: Missing required parameters', ['ip' => $request->ip()]);
                return response()->json([
                    'message' => 'Please provide lat1, lon1, lat2, and lon2',
                    'status' => 201,
                ], 422);
            }

            // /** @var \App\Models\Farmer $farmer */
            $farmer = auth('farmer')->user();
            Log::info('CalculateDistance auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? ($farmer->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$farmer || !$farmer->is_active) {
                Log::warning('CalculateDistance: Authentication failed or farmer inactive', [
                    'farmer_id' => $farmer ? $farmer->id : null,
                    'is_active' => $farmer ? $farmer->is_active : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'lat1' => 'required|numeric',
                'lon1' => 'required|numeric',
                'lat2' => 'required|numeric',
                'lon2' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                Log::warning('CalculateDistance: Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $lat1 = $request->input('lat1');
            $lon1 = $request->input('lon1');
            $lat2 = $request->input('lat2');
            $lon2 = $request->input('lon2');

            // Calculate distance using Haversine formula
            $latFrom = deg2rad($lat1);
            $lonFrom = deg2rad($lon1);
            $latTo = deg2rad($lat2);
            $lonTo = deg2rad($lon2);

            $latDelta = $latTo - $latFrom;
            $lonDelta = $lonTo - $lonFrom;
            $distance = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
            $distanceMeters = $distance * 6371000; // Distance in meters
            $distanceKm = round($distanceMeters / 1000, 2); // Convert to kilometers

            Log::info('CalculateDistance: Distance calculated', [
                'lat1' => $lat1,
                'lon1' => $lon1,
                'lat2' => $lat2,
                'lon2' => $lon2,
                'distance_km' => $distanceKm,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => [
                    'distance_meters' => round($distanceMeters, 2),
                    'distance_km' => $distanceKm,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in calculateDistance', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error calculating distance: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

     public function DoctorOnCall(Request $request)
    {
        Log::info('DoctorOnCall request', [
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'radius' => $request->input('radius'),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        try {
            // Check if required parameters are provided
            if (!$request->has(['latitude', 'longitude', 'radius'])) {
                Log::warning('DoctorOnCall: Missing required parameters', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Please provide latitude, longitude, and radius',
                    'status' => 201,
                ], 422);
            }

            // Validate authentication header
            $token = $request->header('Authentication');
            $validator = Validator::make(['Authentication' => $token], [
                'Authentication' => 'required|string',
            ], [
                'Authentication.required' => 'Authentication token is required',
            ]);

            if ($validator->fails()) {
                Log::warning('DoctorOnCall: Validation failed for authentication', [
                    'errors' => $validator->errors(),
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::info('DoctorOnCall: Auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? $farmer->is_active : null,
                'authentication_header' => $token,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('DoctorOnCall: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'radius' => 'required|numeric|min:0|max:40',
            ]);

            if ($validator->fails()) {
                Log::warning('DoctorOnCall: Input validation failed', [
                    'errors' => $validator->errors(),
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $radius = $request->input('radius');

            // Fetch doctors
            $doctors = Doctor::where('is_active', 1)
                             ->where('is_approved', 1)
                             ->where('is_expert', 0)
                             ->whereNotNull('latitude')
                             ->whereNotNull('longitude')
                             ->get();

            $en_data = [];
            $hi_data = [];
            $pn_data = [];
            $mr_data = [];

            foreach ($doctors as $doctor) {
                $distanceMeters = $this->distance($latitude, $longitude, $doctor->latitude, $doctor->longitude);
                $distanceKm = (int) ($distanceMeters / 1000); // Convert to kilometers, truncate to integer

                if ($distanceKm <= $radius) {
                    $image = !empty($doctor->image) ? asset($doctor->image) : '';

                    $common_data = [
                        'id' => $doctor->id,
                        'email' => $doctor->email,
                        'qualification' => $doctor->qualification,
                        'expertise' => $doctor->expertise,
                        'phone' => $doctor->phone,
                        'type' => $doctor->type,
                        'image' => $image,
                        'km' => $distanceKm,
                    ];

                    $en_data[] = array_merge($common_data, [
                        'name' => $doctor->name,
                    ]);

                    $hi_data[] = array_merge($common_data, [
                        'name' => $doctor->hi_name,
                    ]);

                    $pn_data[] = array_merge($common_data, [
                        'name' => $doctor->pn_name,
                    ]);

                    $mr_data[] = array_merge($common_data, [
                        'name' => $doctor->mr_name,
                    ]);
                }
            }

            $data = [
                'en' => $en_data,
                'hi' => $hi_data,
                'pn' => $pn_data,
                'mr' => $mr_data,
            ];

            Log::info('DoctorOnCall: Query results', [
                'farmer_id' => $farmer->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius' => $radius,
                'doctors_count' => count($en_data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DoctorOnCall: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'latitude' => $request->input('latitude'),
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('DoctorOnCall: Error', [
                'farmer_id' => $farmer->id ?? null,
                'latitude' => $request->input('latitude'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving doctors: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function toolsExpertAdvice(Request $request)
    {
        Log::info('expertAdvice request', [
            'expert_id' => $request->input('expert_id'),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        try {
            // Check if expert_id is provided
            if (!$request->has('expert_id')) {
                Log::warning('expertAdvice: Missing expert_id parameter', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Please provide expert_id',
                    'status' => 201,
                ], 422);
            }

            // Validate authentication header
            $token = $request->header('Authentication');
            $validator = Validator::make(['Authentication' => $token], [
                'Authentication' => 'required|string',
            ], [
                'Authentication.required' => 'Authentication token is required',
            ]);

            if ($validator->fails()) {
                Log::warning('expertAdvice: Validation failed for authentication', [
                    'errors' => $validator->errors(),
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::info('expertAdvice: Auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? $farmer->is_active : null,
                'authentication_header' => $token,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('expertAdvice: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'expert_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                Log::warning('expertAdvice: Input validation failed', [
                    'errors' => $validator->errors(),
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $expert_id = $request->input('expert_id');

            // Fetch expert doctors
            $doctors = Doctor::where('is_active', 1)
                             ->where('is_approved', 1)
                             ->where('is_expert', 1)
                             ->get();

            $en_data = [];
            $hi_data = [];
            $pn_data = [];
            $mr_data = [];

            foreach ($doctors as $doctor) {
                $expert_category = [];

                // Handle expert_category based on its type
                if (!empty($doctor->expert_category)) {
                    if (is_array($doctor->expert_category)) {
                        $expert_category = $doctor->expert_category;
                    } elseif (is_string($doctor->expert_category)) {
                        $decoded = json_decode($doctor->expert_category, true);
                        $expert_category = is_array($decoded) ? $decoded : [];
                    }
                }

                if (is_array($expert_category) && in_array($expert_id, $expert_category)) {
                    $image = !empty($doctor->image) ? asset($doctor->image) : '';
                    $state = State::where('id', $doctor->state)->first();

                    $common_data = [
                        'id' => $doctor->id,
                        'email' => $doctor->email,
                        'degree' => $doctor->degree,
                        'phone' => $doctor->phone,
                        'type' => $doctor->type,
                        'experience' => $doctor->experience,
                        'fees' => $doctor->fees,
                        'expertise' => $doctor->expertise,
                        'qualification' => $doctor->qualification,
                        'district' => $doctor->district,
                        'city' => $doctor->city,
                        'state' => $state ? $state->state_name : '',
                        'image' => $image,
                    ];

                    $en_data[] = array_merge($common_data, [
                        'name' => $doctor->name,
                        'district' => $doctor->district,
                        'city' => $doctor->city,
                    ]);

                    $hi_data[] = array_merge($common_data, [
                        'name' => $doctor->hi_name,
                        'district' => $doctor->hi_district,
                        'city' => $doctor->hi_city,
                    ]);

                    $pn_data[] = array_merge($common_data, [
                        'name' => $doctor->pn_name,
                        'district' => $doctor->pn_district,
                        'city' => $doctor->pn_city,
                    ]);

                    $mr_data[] = array_merge($common_data, [
                        'name' => $doctor->mr_name,
                        'district' => $doctor->mr_district ?? '',
                        'city' => $doctor->mr_city,
                    ]);
                }
            }

            $data = [
                'en' => $en_data,
                'hi' => $hi_data,
                'pn' => $pn_data,
                'mr' => $mr_data,
            ];

            Log::info('expertAdvice: Query results', [
                'farmer_id' => $farmer->id,
                'expert_id' => $expert_id,
                'doctors_count' => count($en_data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('expertAdvice: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'expert_id' => $request->input('expert_id'),
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('expertAdvice: Error', [
                'farmer_id' => $farmer->id ?? null,
                'expert_id' => $request->input('expert_id'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving experts: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function requestDoctor(Request $request)
    {
        try {
            if (!$request->has(['doctor_id', 'is_expert'])) {
                Log::warning('RequestDoctor: Missing required parameters', ['ip' => $request->ip()]);
                return response()->json([
                    'message' => 'Please provide doctor_id and is_expert',
                    'status' => 201,
                ], 422);
            }

            // /** @var \App\Models\Farmer $farmer */
            $farmer = auth('farmer')->user();
            Log::info('RequestDoctor auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? ($farmer->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$farmer || !$farmer->is_active) {
                Log::warning('RequestDoctor: Authentication failed or farmer inactive', [
                    'farmer_id' => $farmer ? $farmer->id : null,
                    'is_active' => $farmer ? $farmer->is_active : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|integer|exists:tbl_doctor,id',
                'is_expert' => 'required|boolean',
                'reason' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'fees' => 'nullable|numeric|min:0',
                'image1' => 'nullable|image|mimes:jpeg,png,jpg|max:25600', // 25MB
                'image2' => 'nullable|image|mimes:jpeg,png,jpg|max:25600',
                'image3' => 'nullable|image|mimes:jpeg,png,jpg|max:25600',
                'image4' => 'nullable|image|mimes:jpeg,png,jpg|max:25600',
                'image5' => 'nullable|image|mimes:jpeg,png,jpg|max:25600',
            ]);

            if ($validator->fails()) {
                Log::warning('RequestDoctor: Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $doctor_id = $request->input('doctor_id');
            $is_expert = $request->input('is_expert');
            $reason = $request->input('reason');
            $description = $request->input('description');
            $fees = $request->input('fees');

            // Handle image uploads
            $images = [];
            $image_fields = ['image1', 'image2', 'image3', 'image4', 'image5'];
            foreach ($image_fields as $index => $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = 'upload_' . $field . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('uploads/export_doctor', $filename, 'public');
                    $images["image" . ($index + 1)] = $path ? '/storage/' . $path : '';
                } else {
                    $images["image" . ($index + 1)] = '';
                }
            }

            // Prepare doctor request data
            $txn_id = mt_rand(999999, 999999999999);
            $cur_date = now()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');
            $cur_date2 = now()->setTimezone('Asia/Kolkata')->format('d-m-Y');

            $request_data = [
                'farmer_id' => $farmer->id,
                'is_expert' => $is_expert,
                'doctor_id' => $doctor_id,
                'reason' => $reason,
                'description' => $description,
                'fees' => $fees,
                'payment_status' => 0,
                'status' => 0,
                'image1' => $images['image1'],
                'image2' => $images['image2'],
                'image3' => $images['image3'],
                'image4' => $images['image4'],
                'image5' => $images['image5'],
                'req_date' => $cur_date2,
                'txn_id' => $txn_id,
                'date' => $cur_date,
                'gateway' => 'CC Avenue',
            ];

            $doctor_request = DoctorRequest::create($request_data);
            $req_id = $doctor_request->id;

            // Verify doctor exists
            $doctor = Doctor::where('id', $doctor_id)->first();
            if (!$doctor) {
                Log::warning('RequestDoctor: Doctor not found', ['doctor_id' => $doctor_id]);
                return response()->json([
                    'message' => 'Doctor not found!',
                    'status' => 201,    
                ], 404);
            }

            // Prepare payment data (CCAvenue placeholder)
            $success_url = url('/api/tools/doctor-payment-success');
            $fail_url = url('/api/tools/payment-failed');

            $post = [
                'txn_id' => '',
                'merchant_id' => env('CCAVENUE_MERCHANT_ID', 'YOUR_MERCHANT_ID'),
                'order_id' => $txn_id,
                'amount' => $fees ?? 0,
                'currency' => 'INR',
                'redirect_url' => $success_url,
                'cancel_url' => $fail_url,
                'billing_name' => $farmer->name ?? 'Unknown',
                'billing_address' => $farmer->village ?? '',
                'billing_city' => $farmer->city ?? '',
                'billing_state' => $farmer->state ?? '',
                'billing_zip' => $farmer->pincode ?? '',
                'billing_country' => 'India',
                'billing_tel' => $farmer->phone ?? '',
                'billing_email' => $farmer->email ?? '',
                'merchant_param1' => 'Doctor Payment',
            ];

            // CCAvenue encryption (placeholder - requires actual keys)
            $merchant_data = http_build_query($post);
            $working_key = env('CCAVENUE_WORKING_KEY', 'YOUR_WORKING_KEY');
            $access_code = env('CCAVENUE_ACCESS_CODE', 'YOUR_ACCESS_CODE');

            // Basic encryption simulation (replace with actual CCAvenue logic)
            $encrypted_data = base64_encode($merchant_data); // Placeholder: Implement actual AES-128-CBC encryption

            $send = [
                'order_id' => $req_id,
                'access_code' => $access_code,
                'redirect_url' => $success_url,
                'cancel_url' => $fail_url,
                'enc_val' => $encrypted_data,
                'plain' => $merchant_data,
                'merchant_param1' => 'Doctor Payment',
            ];

            Log::info('RequestDoctor: Doctor request created', [
                'req_id' => $req_id,
                'farmer_id' => $farmer->id,
                'doctor_id' => $doctor_id,
                'txn_id' => $txn_id,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $send,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in requestDoctor', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
    // /**
    //  * Calculate the distance between two geographic coordinates in meters.
    //  *
    //  * @param float $lat1
    //  * @param float $lon1
    //  * @param float $lat2
    //  * @param float $lon2
    //  * @return float
    //  */


    public function phonePeRequestDoctor(Request $request)
    {
        try {
            if (!$request->has(['doctor_id', 'is_expert'])) {
                Log::warning('PhonePeRequestDoctor: Missing required parameters', ['ip' => $request->ip()]);
                return response()->json([
                    'message' => 'Please provide doctor_id and is_expert',
                    'status' => 201,
                ], 422);
            }

            /** @var \App\Models\Farmer $farmer */
            $farmer = auth('farmer')->user();
            Log::info('PhonePeRequestDoctor auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? ($farmer->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$farmer || !$farmer->is_active) {
                Log::warning('PhonePeRequestDoctor: Authentication failed or farmer inactive', [
                    'farmer_id' => $farmer ? $farmer->id : null,
                    'is_active' => $farmer ? $farmer->is_active : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'doctor_id' => 'required|integer|exists:tbl_doctor,id',
                'is_expert' => 'required|boolean',
                'reason' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:1000',
                'fees' => 'nullable|numeric|min:0',
                'image1' => 'nullable|image|mimes:jpeg,png,jpg|max:25600', // 25MB
                'image2' => 'nullable|image|mimes:jpeg,png,jpg|max:25600',
                'image3' => 'nullable|image|mimes:jpeg,png,jpg|max:25600',
                'image4' => 'nullable|image|mimes:jpeg,png,jpg|max:25600',
                'image5' => 'nullable|image|mimes:jpeg,png,jpg|max:25600',
            ]);

            if ($validator->fails()) {
                Log::warning('PhonePeRequestDoctor: Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $doctor_id = $request->input('doctor_id');
            $is_expert = $request->input('is_expert');
            $reason = $request->input('reason');
            $description = $request->input('description');
            $fees = $request->input('fees');

            // Handle image uploads
            $images = [];
            $image_fields = ['image1', 'image2', 'image3', 'image4', 'image5'];
            foreach ($image_fields as $index => $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $filename = 'upload_' . $field . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('uploads/export_doctor', $filename, 'public');
                    $images["image" . ($index + 1)] = $path ? '/storage/' . $path : '';
                } else {
                    $images["image" . ($index + 1)] = '';
                }
            }

            // Prepare doctor request data
            $txn_id = bin2hex(random_bytes(12)); // Matches CodeIgniter's random transaction ID
            $cur_date = now()->setTimezone('Asia/Kolkata')->format('Y-m-d H:i:s');
            $cur_date2 = now()->setTimezone('Asia/Kolkata')->format('d-m-Y');

            $request_data = [
                'farmer_id' => $farmer->id,
                'is_expert' => $is_expert,
                'doctor_id' => $doctor_id,
                'reason' => $reason,
                'description' => $description,
                'fees' => $fees,
                'payment_status' => 0,
                'status' => 0,
                'image1' => $images['image1'],
                'image2' => $images['image2'],
                'image3' => $images['image3'],
                'image4' => $images['image4'],
                'image5' => $images['image5'],
                'req_date' => $cur_date2,
                'txn_id' => $txn_id,
                'date' => $cur_date,
                'gateway' => 'PhonePe',
            ];

            $doctor_request = DoctorRequest::create($request_data);
            $req_id = $doctor_request->id;

            // Verify doctor exists
            $doctor = Doctor::where('id', $doctor_id)->first();
            if (!$doctor) {
                Log::warning('PhonePeRequestDoctor: Doctor not found', ['doctor_id' => $doctor_id]);
                return response()->json([
                    'message' => 'Doctor not found!',
                    'status' => 201,
                ], 404);
            }

            // PhonePe payment initiation (placeholder)
            $success_url = url('/api/tools/phone-pe-doctor-payment-success');
            $param1 = 'Doctor Payment';

            // Placeholder for initiatePhonePePayment
            $response = $this->initiatePhonePePayment($txn_id, $fees, $farmer->phone, $success_url, $param1);

            if ($response && isset($response['code']) && $response['code'] === 'PAYMENT_INITIATED') {
                $send = [
                    'url' => $response['data']['instrumentResponse']['redirectInfo']['url'] ?? '',
                    'redirect_url' => $success_url,
                    'merchant_param1' => $param1,
                    'order_id' => $req_id,
                ];

                Log::info('PhonePeRequestDoctor: Payment initiated', [
                    'req_id' => $req_id,
                    'farmer_id' => $farmer->id,
                    'doctor_id' => $doctor_id,
                    'txn_id' => $txn_id,
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $send,
                ], 200);
            } else {
                Log::warning('PhonePeRequestDoctor: Payment initiation failed', [
                    'req_id' => $req_id,
                    'farmer_id' => $farmer->id,
                    'response' => $response,
                ]);

                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in phonePeRequestDoctor', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

   
    protected function initiatePhonePePayment($txn_id, $amount, $phone, $redirect_url, $merchant_param1)
    {
        // Placeholder: Implement actual PhonePe payment initiation
        try {
            $merchant_id = env('PHONEPE_MERCHANT_ID', 'YOUR_MERCHANT_ID');
            $salt_key = env('PHONEPE_SALT_KEY', 'YOUR_SALT_KEY');
            $salt_index = env('PHONEPE_SALT_INDEX', '1');
            $api_url = env('PHONEPE_API_URL', 'https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay');

            $payload = [
                'merchantId' => $merchant_id,
                'merchantTransactionId' => $txn_id,
                'merchantUserId' => 'MUID' . $phone,
                'amount' => (int) ($amount * 100), // Convert to paise
                'redirectUrl' => $redirect_url,
                'redirectMode' => 'REDIRECT',
                'callbackUrl' => $redirect_url,
                'mobileNumber' => $phone,
                'paymentInstrument' => [
                    'type' => 'PAY_PAGE',
                ],
            ];

            $payload_base64 = base64_encode(json_encode($payload));
            $checksum = hash('sha256', $payload_base64 . '/pg/v1/pay' . $salt_key) . '###' . $salt_index;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-VERIFY' => $checksum,
            ])->post($api_url, [
                'request' => $payload_base64,
            ]);

            $response_data = $response->json();

            if ($response->successful() && isset($response_data['code']) && $response_data['code'] === 'PAYMENT_INITIATED') {
                return [
                    'code' => 'PAYMENT_INITIATED',
                    'data' => [
                        'instrumentResponse' => [
                            'redirectInfo' => [
                                'url' => $response_data['data']['instrumentResponse']['redirectInfo']['url'] ?? '',
                            ],
                        ],
                    ],
                ];
            }

            Log::warning('PhonePe payment initiation failed', [
                'txn_id' => $txn_id,
                'response' => $response_data,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error initiating PhonePe payment', [
                'txn_id' => $txn_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }




   public function expertCategory(Request $request)
    {
        Log::info('expertCategory request', [
            'lang' => $request->header('Lang', 'en'),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        try {
            // Validate headers
            $token = $request->header('Authentication');
            $lang = $request->header('Lang', 'en');
            $validator = Validator::make([
                'Authentication' => $token,
                'Lang' => $lang,
            ], [
                'Authentication' => 'required|string',
                'Lang' => 'nullable|string|in:en,hi,mr,pn',
            ], [
                'Authentication.required' => 'Authentication token is required',
                'Lang.in' => 'The Lang header must be one of: en, hi, mr, pn',
            ]);

            if ($validator->fails()) {
                Log::warning('expertCategory: Validation failed for headers', [
                    'errors' => $validator->errors(),
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::info('expertCategory: Auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? $farmer->is_active : null,
                'authentication_header' => $token,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('expertCategory: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Determine language, default to 'en' if invalid
            $language = in_array($lang, ['en', 'hi', 'mr', 'pn']) ? $lang : 'en';

            // Fetch active expertise categories
            $categories = ExpertiseCategory::where('is_active', 1)->get();

            $category_data = [];
            foreach ($categories as $category) {
                $cat_image = '';
                switch ($language) {
                    case 'hi':
                        $cat_image = $category->image_hindi ? asset($category->image_hindi) : '';
                        break;
                    case 'mr':
                        $cat_image = $category->image_marathi ? asset($category->image_marathi) : '';
                        break;
                    case 'pn':
                        $cat_image = $category->image_punjabi ? asset($category->image_punjabi) : '';
                        break;
                    case 'en':
                    default:
                        $cat_image = $category->image ? asset($category->image) : '';
                        break;
                }

                $category_data[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $cat_image,
                ];
            }

            Log::info('expertCategory: Query results', [
                'farmer_id' => $farmer->id,
                'language' => $language,
                'categories_count' => count($category_data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $category_data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('expertCategory: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'lang' => $request->header('Lang', 'en'),
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('expertCategory: Error', [
                'farmer_id' => $farmer->id ?? null,
                'lang' => $request->header('Lang', 'en'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving categories: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }


    public function getVendors(Request $request)
    {
        try {
            // /** @var \App\Models\Farmer $farmer */
            $farmer = auth('farmer')->user();
            Log::info('GetVendors auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'is_active' => $farmer ? ($farmer->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$farmer || !$farmer->is_active) {
                Log::warning('GetVendors: Authentication failed or farmer inactive', [
                    'farmer_id' => $farmer ? $farmer->id : null,
                    'is_active' => $farmer ? $farmer->is_active : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'required|numeric|min:0|max:60',
            ]);

            if ($validator->fails()) {
                Log::warning('GetVendors: Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $latitude = (float) $request->input('latitude');
            $longitude = (float) $request->input('longitude');
            $radius = (float) $request->input('radius');

            $vendors = Vendor::where('is_active', 1)
                            ->where('is_approved', 1)
                            ->whereNotNull('latitude')
                            ->whereNotNull('longitude')
                            ->get();

            $en_data = [];
            $hi_data = [];
            $pn_data = [];
            $mr_data = [];

            foreach ($vendors as $vendor) {
                $distance = $this->calculateDistanceInKm(
                    $latitude,
                    $longitude,
                    (float) $vendor->latitude,
                    (float) $vendor->longitude
                );
                $formatted_distance = (int) $distance;

                if ($distance <= $radius) {
                    $state = State::where('id', $vendor->state)->first();

                    $common_data = [
                        'vendor_id' => $vendor->id,
                        'address' => $vendor->address,
                        'district' => $vendor->district,
                        'city' => $vendor->city,
                        'state' => $state ? $state->state_name : null,
                        'pincode' => $vendor->pincode,
                        'km' => $formatted_distance,
                    ];

                    $en_data[] = array_merge($common_data, [
                        'name' => $vendor->name,
                        'shop_name' => $vendor->shop_name,
                    ]);

                    $hi_data[] = array_merge($common_data, [
                        'name' => $vendor->hi_name,
                        'shop_name' => $vendor->shop_hi_name,
                    ]);

                    $pn_data[] = array_merge($common_data, [
                        'name' => $vendor->pn_name,
                        'shop_name' => $vendor->shop_pn_name,
                    ]);

                    $mr_data[] = array_merge($common_data, [
                        'name' => $vendor->mr_name,
                        'shop_name' => $vendor->shop_mr_name,
                    ]);
                }
            }

            $data = [
                'en' => $en_data,
                'hi' => $hi_data,
                'pn' => $pn_data,
                'mr' => $mr_data,
            ];

            Log::info('GetVendors: Query results', [
                'farmer_id' => $farmer->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'radius' => $radius,
                'vendors_count' => count($en_data),
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getVendors', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving vendors: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
    public function vendorAllProducts(Request $request)
    {
        try {
            // /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('VendorAllProducts auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$vendor || !$vendor->is_active) {
                Log::warning('VendorAllProducts: Authentication failed or vendor inactive', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'is_admin' => 'required|string',
                'vendor_id' => 'nullable|integer|exists:tbl_vendor,id',
                'search' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('VendorAllProducts: Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $is_admin = $request->input('is_admin');
            $vendor_id = $request->input('vendor_id');
            $search = $request->input('search');
            $page_index = (int) $request->header('Index', 1);
            $limit = 25;
            $start = ($page_index - 1) * $limit;

            $query = Product::query()->where('is_active', 1);

            if ($is_admin === 'admin') {
                $query->where('is_admin', 1)->whereIn('show_product', [1, 2]);
            } else {
                $query->where('is_admin', 0)
                      ->where('is_approved', 1)
                      ->where('added_by', $vendor_id ?: $vendor->id)
                      ->whereIn('show_product', [1, 2]);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name_english', 'LIKE', '%' . $search . '%')
                      ->orWhere('name_hindi', 'LIKE', '%' . $search . '%')
                      ->orWhere('name_punjabi', 'LIKE', '%' . $search . '%');
                });
            }

            $count = $query->count();
            $pages = (int) ceil($count / $limit);
            $products = $query->offset($start)->limit($limit)->get();

            $en_data = [];
            $hi_data = [];
            $pn_data = [];

            foreach ($products as $pro) {
                $image = [];
                if (!empty($pro->image)) {
                    $imageArray = json_decode($pro->image, true);
                    if (is_array($imageArray) && !empty($imageArray)) {
                        foreach ($imageArray as $img) {
                            $image[] = url($img);
                        }
                    } else {
                        $image[] = url($pro->image);
                    }
                }

                $video = !empty($pro->video) ? url($pro->video) : '';
                $stock = $pro->inventory != 0 ? 'In Stock' : 'Out of Stock';
                $discount = (int)$pro->mrp - (int)$pro->selling_price;
                $percent = $discount > 0 ? round($discount / $pro->mrp * 100) : 0;

                $cart = Cart::where('vendor_id', $vendor->id)
                            ->where('product_id', $pro->id)
                            ->first();
                $cart_qty = $cart ? $cart->qty : null;

                $common_data = [
                    'pro_id' => $pro->id,
                    'image' => $image,
                    'video' => $video,
                    'cart_qty' => $cart_qty,
                    'vendor_mrp' => $pro->vendor_mrp,
                    'vendor_min_qty' => $pro->vendor_min_qty ?: 1,
                    'vendor_selling_price' => $pro->vendor_selling_price,
                    'suffix' => $pro->suffix,
                    'stock' => $stock,
                    'percent' => $percent,
                    'vendor_id' => $pro->added_by,
                    'is_admin' => $pro->is_admin,
                    'offer' => $pro->offer,
                    'product_cod' => $pro->cod,
                    'is_cod' => $vendor->cod,
                ];

                $en_data[] = array_merge($common_data, [
                    'name' => $pro->name_english,
                    'description' => $pro->description_english,
                ]);

                $hi_data[] = array_merge($common_data, [
                    'name' => $pro->name_hindi,
                    'description' => $pro->description_hindi,
                ]);

                $pn_data[] = array_merge($common_data, [
                    'name' => $pro->name_punjabi,
                    'description' => $pro->description_punjabi,
                ]);
            }

            $data = [
                'en' => $en_data,
                'hi' => $hi_data,
                'pn' => $pn_data,
            ];

            $pagination = $this->createPagination($page_index, $pages);

            Log::info('VendorAllProducts: Query results', [
                'vendor_id' => $vendor->id,
                'is_admin' => $is_admin,
                'search' => $search,
                'page_index' => $page_index,
                'products_count' => count($en_data),
                'total_pages' => $pages,
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'is_cod' => $vendor->cod,
                'pagination' => $pagination,
                'last' => $pages,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in vendorAllProducts', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving products: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    
    protected function distance($lat1, $lon1, $lat2, $lon2)
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $distance = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $distance * 6371000; // Distance in meters
    }
}
