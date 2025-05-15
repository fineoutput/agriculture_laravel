<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\AnimalCycle;
use App\Models\Farmer;
use App\Models\DailyRecord;
use App\Models\StockHandling;
use App\Models\Disease;
use App\Models\Tank;
use App\Models\Canister;
use App\Models\MedicalExpense;
use App\Models\DoctorRequest;
use App\Models\MilkRecord;
use App\Models\BreedingRecord;
use App\Models\EquipmentSalePurchase;
use App\Models\Group;
use App\Models\MyAnimal;
use App\Models\SalePurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class ManagementController extends Controller
{
    private function createPagination($currentPage, $totalPages)
    {
        $maxPagesToShow = 5; // Show up to 5 page numbers
        $pagination = [];

        $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
        $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);

        // Adjust startPage if endPage is at the maximum
        if ($endPage - $startPage + 1 < $maxPagesToShow) {
            $startPage = max(1, $endPage - $maxPagesToShow + 1);
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $pagination[] = $i;
        }

        return $pagination;
    }
    public function dailyRecords(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('DailyRecords: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'date' => 'required|date_format:d-m-Y',
                'data' => 'required|json',
                'update_inventory' => 'required|string|in:Yes,No',
            ]);

            if ($validator->fails()) {
                Log::warning('DailyRecords: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('DailyRecords: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('DailyRecords: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $date = $request->input('date');
            $data = json_decode($request->input('data'));
            $updateInventory = $request->input('update_inventory');
            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            if (!is_array($data)) {
                Log::warning('DailyRecords: Invalid data format', [
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Data must be a valid JSON array',
                    'status' => 201,
                ], 422);
            }

            // Validate inventory for feed types
            foreach ($data as $item) {
                if (
                    !empty($item->values->qty) &&
                    !empty($item->values->price) &&
                    !empty($item->values->amount) &&
                    $updateInventory === 'Yes' &&
                    $item->values->type === 'feed' &&
                    $item->column !== 'feed'
                ) {
                    $stock = StockHandling::where('farmer_id', $farmer->id)
                        ->sum($item->column);

                    if ($stock < $item->values->qty) {
                        Log::warning('DailyRecords: Insufficient inventory', [
                            'farmer_id' => $farmer->id,
                            'column' => $item->column,
                            'available' => $stock,
                            'required' => $item->values->qty,
                            'ip' => $request->ip(),
                        ]);
                        return response()->json([
                            'message' => "{$item->column} available inventory is {$stock}",
                            'status' => 201,
                        ], 422);
                    }
                }
            }

            $entryId = bin2hex(random_bytes(5));

            DB::transaction(function () use ($data, $farmer, $date, $updateInventory, $currentDateTime, $entryId) {
                foreach ($data as $item) {
                    if (
                        !empty($item->values->qty) &&
                        !empty($item->values->price) &&
                        !empty($item->values->amount)
                    ) {
                        // Insert daily record
                        DailyRecord::create([
                            'record_date' => $date,
                            'farmer_id' => $farmer->id,
                            'entry_id' => $entryId,
                            'name' => $item->name,
                            'type' => $item->values->type,
                            'qty' => $item->values->qty,
                            'price' => $item->values->price,
                            'amount' => $item->values->amount,
                            'update_inventory' => $updateInventory,
                            'date' => $currentDateTime,
                        ]);

                        // Update inventory if required
                        if (
                            $updateInventory === 'Yes' &&
                            $item->values->type === 'feed' &&
                            $item->column !== 'feed'
                        ) {
                            StockHandling::create([
                                'farmer_id' => $farmer->id,
                                $item->column => -$item->values->qty,
                                'date' => $currentDateTime,
                                'is_txn' => 1,
                            ]);
                        }
                    }
                }
            });

            Log::info('DailyRecords: Records inserted successfully', [
                'farmer_id' => $farmer->id,
                'entry_id' => $entryId,
                'record_count' => count($data),
                'update_inventory' => $updateInventory,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DailyRecords: Database error', [
                'farmer_id' => $farmer->id ?? null,
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
            Log::error('DailyRecords: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewDailyRecords(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');
            $pageIndex = $request->header('Index', 1); // Default to page 1 if not provided

            if (empty($authToken)) {
                Log::warning('ViewDailyRecords: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('ViewDailyRecords: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $limit = 20;
            $pageIndex = max(1, (int) $pageIndex); // Ensure pageIndex is at least 1
            $offset = ($pageIndex - 1) * $limit;

            // Count distinct entry_ids for pagination
            $count = DailyRecord::where('farmer_id', $farmer->id)
                ->distinct('entry_id')
                ->count('entry_id');

            // Calculate total pages
            $totalPages = ceil($count / $limit);

            // Fetch distinct entry_ids for the current page
           $subQuery = DailyRecord::select('entry_id', 'id')
    ->where('farmer_id', $farmer->id)
    ->orderBy('id', 'desc');

$entryIds = DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
    ->mergeBindings($subQuery->getQuery())
    ->groupBy('entry_id')
    ->skip($offset)
    ->take($limit)
    ->pluck('entry_id');


            // Fetch all records for the selected entry_ids
            $big = [];
            foreach ($entryIds as $entryId) {
                $records = DailyRecord::where('entry_id', $entryId)
                    ->where('farmer_id', $farmer->id)
                    ->get();

                $data = [];
                foreach ($records as $record) {
                    $data[] = [
                        'record_date' => Carbon::parse($record->record_date)->format('d-m-Y'),
                        'name' => $record->name,
                        'qty' => $record->qty,
                        'price' => $record->price,
                        'amount' => $record->amount,
                        'date' => Carbon::parse($record->date)->format('d/m/Y'),
                    ];
                }

                $big[] = $data;
            }

            // Generate pagination metadata
            $pagination = $this->createPagination($pageIndex, $totalPages);

            Log::info('ViewDailyRecords: Records retrieved successfully', [
                'farmer_id' => $farmer->id,
                'page_index' => $pageIndex,
                'record_count' => count($big),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $big,
                'pagination' => $pagination,
                'last' => $totalPages,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ViewDailyRecords: Error retrieving records', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving records: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function milkRecords(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('MilkRecords: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'information_type' => 'required|string',
                'group_id' => 'nullable|string',
                'tag_no' => 'nullable|string',
                'milking_slot' => 'required|string',
                'milk_date' => 'required|date_format:Y-m-d',
                'entry_milk' => 'required|numeric|min:0',
                'price_milk' => 'required|numeric|min:0',
                'fat' => 'required|numeric|min:0',
                'snf' => 'required|numeric|min:0',
                'total_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                Log::warning('MilkRecords: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('MilkRecords: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('MilkRecords: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            $milkRecord = MilkRecord::create([
                'farmer_id' => $farmer->id,
                'information_type' => $request->input('information_type'),
                'group_id' => $request->input('group_id'),
                'tag_no' => $request->input('tag_no'),
                'milking_slot' => $request->input('milking_slot'),
                'milk_date' => $request->input('milk_date'),
                'entry_milk' => $request->input('entry_milk'),
                'price_milk' => $request->input('price_milk'),
                'fat' => $request->input('fat'),
                'snf' => $request->input('snf'),
                'total_price' => $request->input('total_price'),
                'date' => $currentDateTime,
            ]);

            Log::info('MilkRecords: Record inserted successfully', [
                'farmer_id' => $farmer->id,
                'milk_record_id' => $milkRecord->id,
                'milk_date' => $request->input('milk_date'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
                'data' => [],
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('MilkRecords: Database error', [
                'farmer_id' => $farmer->id ?? null,
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
            Log::error('MilkRecords: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewMilkRecords(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');
            $pageIndex = $request->header('Index', 1); // Default to page 1 if not provided

            if (empty($authToken)) {
                Log::warning('ViewMilkRecords: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('ViewMilkRecords: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $limit = 20;
            $pageIndex = max(1, (int) $pageIndex); // Ensure pageIndex is at least 1
            $offset = ($pageIndex - 1) * $limit;

            // Count total records for pagination
            $count = MilkRecord::where('farmer_id', $farmer->id)->count();

            // Calculate total pages
            $totalPages = ceil($count / $limit);

            // Fetch milk records for the current page
            $milkRecords = MilkRecord::where('farmer_id', $farmer->id)
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            // Prepare response data
            $data = [];
            $serialNumber = 1;

            foreach ($milkRecords as $milk) {
                // Fetch group name if group_id exists
                $groupName = '';
                if (!empty($milk->group_id)) {
                    $group = Group::where('id', $milk->group_id)->first();
                    $groupName = $group ? $group->name : '';
                }

                $data[] = [
                    's_no' => $serialNumber,
                    'information_type' => $milk->information_type,
                    'group' => $groupName,
                    'tag_no' => $milk->tag_no,
                    'milking_slot' => $milk->milking_slot,
                    'milk_date' => $milk->milk_date,
                    'entry_milk' => $milk->entry_milk,
                    'price_milk' => $milk->price_milk,
                    'fat' => $milk->fat,
                    'snf' => $milk->snf,
                    'total_price' => $milk->total_price,
                    'date' => Carbon::parse($milk->date)->format('d/m/Y'),
                ];

                $serialNumber++;
            }

            // Generate pagination metadata
            $pagination = $this->createPagination($pageIndex, $totalPages);

            Log::info('ViewMilkRecords: Records retrieved successfully', [
                'farmer_id' => $farmer->id,
                'page_index' => $pageIndex,
                'record_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'pagination' => $pagination,
                'last' => $totalPages,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ViewMilkRecords: Error retrieving records', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving records: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function salePurchase(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('SalePurchase: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'information_type' => 'required|string',
                'animal_name' => 'nullable|string',
                'milk_production' => 'required|numeric|min:0',
                'lactation' => 'required|integer|min:0',
                'pastorate_pregnant' => 'nullable|string',
                'expected_price' => 'required|numeric|min:0',
                'location' => 'required|string',
                'animal_type' => 'required|string',
                'description' => 'required|string',
                'remarks' => 'nullable|string',
                'image1' => 'nullable|file|mimes:jpg,jpeg,png|max:25000',
                'image2' => 'nullable|file|mimes:jpg,jpeg,png|max:25000',
                'image3' => 'nullable|file|mimes:jpg,jpeg,png|max:25000',
                'image4' => 'nullable|file|mimes:jpg,jpeg,png|max:25000',
                'video' => 'nullable|file|mimes:mp4,avi,mov,mpeg,mkv|max:50000',
            ]);

            if ($validator->fails()) {
                Log::warning('SalePurchase: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('SalePurchase: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('SalePurchase: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();
            $timestamp = Carbon::now('Asia/Kolkata')->format('YmdHis');

            // Handle file uploads
            $imagePaths = ['image1' => null, 'image2' => null, 'image3' => null, 'image4' => null];
            $videoPath = null;

            foreach (['image1', 'image2', 'image3', 'image4'] as $index => $imageField) {
                if ($request->hasFile($imageField)) {
                    $file = $request->file($imageField);
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $fileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
                    $file->move(public_path('uploads/sales'), $fileName);
                    $imagePaths[$imageField] = $fileName;
                }
            }

            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = pathinfo($originalName, PATHINFO_FILENAME) . '_' . $timestamp . '.' . $extension;
                $file->move(public_path('uploads/sales/videos'), $fileName);
                $videoPath = $fileName;
            }

            // Prepare data for insertion
            $data = [
                'farmer_id' => $farmer->id,
                'information_type' => $request->input('information_type'),
                'animal_name' => $request->input('animal_name'),
                'milk_production' => $request->input('milk_production'),
                'lactation' => $request->input('lactation'),
                'pastorate_pregnant' => $request->input('pastorate_pregnant'),
                'expected_price' => $request->input('expected_price'),
                'location' => $request->input('location'),
                'animal_type' => $request->input('animal_type'),
                'description' => $request->input('description'),
                'remarks' => $request->input('remarks'),
                'image1' => $imagePaths['image1'],
                'image2' => $imagePaths['image2'],
                'image3' => $imagePaths['image3'],
                'image4' => $imagePaths['image4'],
                'video' => $videoPath,
                'status' => 0,
                'date' => $currentDateTime,
            ];

            // Insert record
            $salePurchase = SalePurchase::create($data);

            // Send email to admin
            try {
                $message = "
                    Hello Admin<br/><br/>
                    You have received a new sale/purchase request from a farmer. Below are the details:<br/><br/>
                    <b>Farmer ID</b> - {$farmer->id}<br/>
                    <b>Farmer Name</b> - {$farmer->name}<br/>
                    <b>Sale/Purchase ID</b> - {$salePurchase->id}<br/>
                    <b>Type</b> - {$request->input('information_type')}<br/>
                    <b>Expected Price</b> - Rs.{$request->input('expected_price')}<br/>
                ";

                Mail::html($message, function ($message) use ($farmer) {
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                            ->to(env('MAIL_TO_ADDRESS'), 'Dairy Muneem')
                            ->subject('New sale/purchase request received from a farmer');
                });
            } catch (\Exception $e) {
                Log::warning('SalePurchase: Failed to send email', [
                    'farmer_id' => $farmer->id,
                    'sale_purchase_id' => $salePurchase->id,
                    'error' => $e->getMessage(),
                    'ip' => $request->ip(),
                ]);
                // Continue even if email fails, as in original code
            }

            Log::info('SalePurchase: Record inserted successfully', [
                'farmer_id' => $farmer->id,
                'sale_purchase_id' => $salePurchase->id,
                'information_type' => $request->input('information_type'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
                'data' => [],
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('SalePurchase: Database error', [
                'farmer_id' => $farmer->id ?? null,
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
            Log::error('SalePurchase: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewSalePurchase(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');
            $pageIndex = $request->header('Index', 1); // Default to page 1 if not provided

            if (empty($authToken)) {
                Log::warning('ViewSalePurchase: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('ViewSalePurchase: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $limit = 20;
            $pageIndex = max(1, (int) $pageIndex); // Ensure pageIndex is at least 1
            $offset = ($pageIndex - 1) * $limit;

            // Count total records for pagination
            $count = SalePurchase::where('farmer_id', $farmer->id)->count();

            // Calculate total pages
            $totalPages = ceil($count / $limit);

            // Fetch sale/purchase records for the current page
            $salePurchases = SalePurchase::where('farmer_id', $farmer->id)
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            // Prepare response data
            $data = [];
            $serialNumber = 1;

            foreach ($salePurchases as $sale) {
                // Map status to label, show, and background color
                switch ($sale->status) {
                    case 0:
                        $status = 'Pending';
                        $show = 0;
                        $bgColor = '#65bcd7';
                        break;
                    case 1:
                        $status = 'Accepted';
                        $show = 1;
                        $bgColor = '#3b71ca';
                        break;
                    case 2:
                        $status = 'Completed';
                        $show = 0;
                        $bgColor = '#139c49';
                        break;
                    case 3:
                        $status = 'Rejected';
                        $show = 0;
                        $bgColor = '#dc4c64';
                        break;
                    default:
                        $status = 'Unknown';
                        $show = 0;
                        $bgColor = '#000000';
                }

                // Generate URLs for files using asset()
                $image1 = $sale->image1 ? asset('uploads/sales/' . $sale->image1) : '';
                $image2 = $sale->image2 ? asset('uploads/sales/' . $sale->image2) : '';
                $image3 = $sale->image3 ? asset('uploads/sales/' . $sale->image3) : '';
                $image4 = $sale->image4 ? asset('Uploads/sales/' . $sale->image4) : '';
                $video = $sale->video ? asset('uploads/sales/videos/' . $sale->video) : '';

                $data[] = [
                    's_no' => $serialNumber,
                    'id' => $sale->id,
                    'information_type' => $sale->information_type,
                    'animal_name' => $sale->animal_name,
                    'milk_production' => $sale->milk_production,
                    'lactation' => $sale->lactation,
                    'location' => $sale->location,
                    'expected_price' => $sale->expected_price,
                    'pastorate_pregnant' => $sale->pastorate_pregnant,
                    'image1' => $image1,
                    'image2' => $image2,
                    'image3' => $image3,
                    'image4' => $image4,
                    'video' => $video,
                    'animal_type' => $sale->animal_type,
                    'description' => $sale->description,
                    'remarks' => $sale->remarks,
                    'show' => $show,
                    'status' => $status,
                    'bg_color' => $bgColor,
                    'date' => Carbon::parse($sale->date)->format('d/m/Y'),
                ];

                $serialNumber++;
            }

            // Generate pagination metadata
            $pagination = $this->createPagination($pageIndex, $totalPages);

            Log::info('ViewSalePurchase: Records retrieved successfully', [
                'farmer_id' => $farmer->id,
                'page_index' => $pageIndex,
                'record_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'pagination' => $pagination,
                'last' => $totalPages,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ViewSalePurchase: Error retrieving records', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving records: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewOthersSalePurchase(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');
            $pageIndex = $request->header('Index', 1);
            $informationType = $request->input('Information_Type', '');
            $latitude = $request->input('Latitude');
            $longitude = $request->input('Longitude');

            // Validate input
            $validator = Validator::make($request->all(), [
                'Latitude' => 'required|numeric',
                'Longitude' => 'required|numeric',
                'Information_Type' => 'nullable|string|in:animal_sale,animal_purchase,equipment_Sale,equipment_purchase',
            ]);

            if ($validator->fails()) {
                Log::warning('ViewOthersSalePurchase: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 400,
                ], 400);
            }

            if (empty($authToken)) {
                Log::warning('ViewOthersSalePurchase: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('ViewOthersSalePurchase: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Determine table and information type mapping
            $table = '';
            $infoTypeValue = '';
            if (!empty($informationType)) {
                if (in_array($informationType, ['animal_sale', 'animal_purchase'])) {
                    $table = SalePurchase::class;
                    $infoTypeValue = $informationType === 'animal_sale' ? 'Sale' : 'Purchase';
                } elseif (in_array($informationType, ['equipment_Sale', 'equipment_purchase'])) {
                    $table = EquipmentSalePurchase::class;
                    $infoTypeValue = $informationType === 'equipment_Sale' ? 'Sale' : 'Purchase';
                }
            }

            if (empty($table)) {
                Log::warning('ViewOthersSalePurchase: Invalid or missing Information_Type', [
                    'ip' => $request->ip(),
                    'information_type' => $informationType,
                ]);
                return response()->json([
                    'message' => 'Invalid or missing Information_Type',
                    'status' => 400,
                ], 400);
            }

            $limit = 20;
            $pageIndex = max(1, (int) $pageIndex);
            $offset = ($pageIndex - 1) * $limit;

            // Count total records
            $query = $table::where('farmer_id', '!=', $farmer->id)
                ->where('status', 1);

            if ($infoTypeValue) {
                $query->where('information_type', $infoTypeValue);
            }

            $count = $query->count();
            $totalPages = ceil($count / $limit);

            // Fetch records
            $records = $table::where('farmer_id', '!=', $farmer->id)
                ->where('status', 1)
                ->when($infoTypeValue, function ($query) use ($infoTypeValue) {
                    return $query->where('information_type', $infoTypeValue);
                })
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->with(['farmer' => function ($query) {
                    $query->select('id', 'name', 'phone', 'image', 'city', 'latitude', 'longitude');
                }])
                ->get();

            // Prepare response data
            $data = [];
            $serialNumber = 1;
            $currentDate = Carbon::now('Asia/Kolkata');

            foreach ($records as $record) {
                $farmerData = $record->farmer;
                $fName = $farmerData ? $farmerData->name : '';
                $fPhone = $farmerData ? $farmerData->phone : '';
                $fImage = $farmerData && $farmerData->image ? asset($farmerData->image) : '';
                $fCity = $farmerData ? $farmerData->city : '';
                $fLatitude = $farmerData ? $farmerData->latitude : null;
                $fLongitude = $farmerData ? $farmerData->longitude : null;

                // Calculate time difference
                $timeDiff = 'No expiration date';
                if ($record->date) {
                    $expDate = Carbon::parse($record->date, 'Asia/Kolkata');
                    $interval = $expDate->diff($currentDate);
                    $minutesDiff = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                    if ($minutesDiff < 60) {
                        $timeDiff = $minutesDiff . ' minutes';
                    } elseif ($minutesDiff < 1440) {
                        $timeDiff = $interval->h . ' hours ' . $interval->i . ' minutes';
                    } else {
                        $timeDiff = $interval->days . ' days';
                    }
                }

                // Calculate distance
                $distance = $this->calculateDistance($fLatitude, $fLongitude, $latitude, $longitude);
                $shortDistance = number_format($distance, 2) . ' km';

                // Format date
                $formattedDate = $record->date ? Carbon::parse($record->date)->format('d/m/Y') : '';

                // Generate file URLs
                $image1 = $image2 = $image3 = $image4 = $video = '';
                $imagePath = $table === SalePurchase::class ? 'uploads/sales/' : 'uploads/equipment/';
                $videoPath = $table === SalePurchase::class ? 'uploads/sales/videos/' : 'uploads/equipment/videos/';

                if ($record->image1) {
                    $image1 = asset($imagePath . $record->image1);
                }
                if ($record->image2) {
                    $image2 = asset($imagePath . $record->image2);
                }
                if ($record->image3) {
                    $image3 = asset($imagePath . $record->image3);
                }
                if ($record->image4) {
                    $image4 = asset($imagePath . $record->image4);
                }
                if ($record->video) {
                    $video = asset($videoPath . $record->video);
                }

                $images = array_filter([$image1, $image2, $image3, $image4, $video]);

                // Table-specific data
                $extraData = [];
                if ($table === SalePurchase::class) {
                    $extraData = [
                        'animal_name' => $record->animal_name ?? '',
                        'milk_production' => $record->milk_production ?? '',
                        'lactation' => $record->lactation ?? '',
                        'location' => $record->location ?? '',
                        'expected_price' => $record->expected_price ?? '',
                        'pastorate_pregnant' => $record->pastorate_pregnant ?? '',
                        'animal_type' => $record->animal_type ?? '',
                        'description' => $record->description ?? '',
                        'remarks' => $record->remarks ?? '',
                    ];
                } elseif ($table === EquipmentSalePurchase::class) {
                    $extraData = [
                        'equipment_type' => $record->equipment_type ?? '',
                        'company_name' => $record->company_name ?? '',
                        'year_old' => $record->year_old ?? '',
                        'price' => $record->price ?? '',
                        'remark' => $record->remark ?? '',
                    ];
                }

                $data[] = array_merge([
                    's_no' => $serialNumber,
                    'id' => $record->id,
                    'information_type' => $record->information_type,
                    'f_name' => $fName,
                    'f_phone' => $fPhone,
                    'f_image' => $fImage,
                    'f_city' => $fCity,
                    'days_diff' => $timeDiff,
                    'date' => $formattedDate,
                    'distance' => $shortDistance,
                    'images' => array_values($images),
                ], $extraData);

                $serialNumber++;
            }

            // Generate pagination metadata
            $pagination = $this->createPagination($pageIndex, $totalPages);

            Log::info('ViewOthersSalePurchase: Records retrieved successfully', [
                'farmer_id' => $farmer->id,
                'page_index' => $pageIndex,
                'information_type' => $informationType,
                'record_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'pagination' => $pagination,
                'last' => $totalPages,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('ViewOthersSalePurchase: Database error', [
                'farmer_id' => $farmer->id ?? null,
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
            Log::error('ViewOthersSalePurchase: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving records: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return 0.0;
        }

        $earthRadius = 6371; // Radius of the Earth in kilometers
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in kilometers
    }

    public function medicalExpenses(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('MedicalExpenses: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('MedicalExpenses: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('MedicalExpenses: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'expense_date' => 'required|date_format:Y-m-d',
                'doctor_visit_fees' => 'nullable|numeric|min:0',
                'treatment_expenses' => 'nullable|numeric|min:0',
                'vaccination_expenses' => 'nullable|numeric|min:0',
                'deworming_expenses' => 'nullable|numeric|min:0',
                'other1' => 'nullable|string|max:255',
                'other2' => 'nullable|string|max:255',
                'other3' => 'nullable|string|max:255',
                'other4' => 'nullable|string|max:255',
                'other5' => 'nullable|string|max:255',
                'total_price' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                Log::warning('MedicalExpenses: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            $data = [
                'farmer_id' => $farmer->id,
                'expense_date' => $request->input('expense_date'),
                'doctor_visit_fees' => $request->input('doctor_visit_fees'),
                'treatment_expenses' => $request->input('treatment_expenses'),
                'vaccination_expenses' => $request->input('vaccination_expenses'),
                'deworming_expenses' => $request->input('deworming_expenses'),
                'other1' => $request->input('other1'),
                'other2' => $request->input('other2'),
                'other3' => $request->input('other3'),
                'other4' => $request->input('other4'),
                'other5' => $request->input('other5'),
                'total_price' => $request->input('total_price'),
                'date' => $currentDateTime,
            ];

            $medicalExpense = MedicalExpense::create($data);

            Log::info('MedicalExpenses: Record inserted successfully', [
                'farmer_id' => $farmer->id,
                'medical_expense_id' => $medicalExpense->id,
                'expense_date' => $request->input('expense_date'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
                'data' => [],
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('MedicalExpenses: Database error', [
                'farmer_id' => $farmer->id ?? null,
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
            Log::error('MedicalExpenses: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewMedicalExpenses(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');
            $pageIndex = $request->header('Index', 1); // Default to page 1 if not provided

            if (empty($authToken)) {
                Log::warning('ViewMedicalExpenses: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('ViewMedicalExpenses: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $limit = 20;
            $pageIndex = max(1, (int) $pageIndex); // Ensure pageIndex is at least 1
            $offset = ($pageIndex - 1) * $limit;

            // Count total records for pagination
            $count = MedicalExpense::where('farmer_id', $farmer->id)->count();

            // Calculate total pages
            $totalPages = ceil($count / $limit);

            // Fetch medical expense records for the current page
            $expenses = MedicalExpense::where('farmer_id', $farmer->id)
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            // Prepare response data
            $data = [];
            $serialNumber = 1;

            foreach ($expenses as $expense) {
                $data[] = [
                    's_no' => $serialNumber,
                    'expense_date' => $expense->expense_date,
                    'doctor_visit_fees' => $expense->doctor_visit_fees,
                    'treatment_expenses' => $expense->treatment_expenses,
                    'vaccination_expenses' => $expense->vaccination_expenses,
                    'deworming_expenses' => $expense->deworming_expenses,
                    'other1' => $expense->other1,
                    'other2' => $expense->other2,
                    'other3' => $expense->other3,
                    'other4' => $expense->other4,
                    'other5' => $expense->other5,
                    'total_price' => $expense->total_price,
                    'date' => Carbon::parse($expense->date)->format('d/m/Y'),
                ];
                $serialNumber++;
            }

            // Generate pagination metadata
            $pagination = $this->createPagination($pageIndex, $totalPages);

            Log::info('ViewMedicalExpenses: Records retrieved successfully', [
                'farmer_id' => $farmer->id,
                'page_index' => $pageIndex,
                'record_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'pagination' => $pagination,
                'last' => $totalPages,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ViewMedicalExpenses: Error retrieving records', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving records: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function reports(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('Reports: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('Reports: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('Reports: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'from' => 'nullable|date_format:Y-m-d',
                'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            ]);

            if ($validator->fails()) {
                Log::warning('Reports: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $from = $request->input('from');
            $to = $request->input('to');

            // Medical Expenses
            $medicalQuery = MedicalExpense::where('farmer_id', $farmer->id);
            if ($from && $to) {
                $medicalQuery->whereBetween('expense_date', [$from, $to]);
            }
            $medicalExp = $medicalQuery->sum('total_price') ?: 0;

            // Doctor Expenses
            $doctorQuery = DoctorRequest::where('farmer_id', $farmer->id)
                ->where('payment_status', '>=', 1);
            if ($from && $to) {
                $doctorQuery->whereBetween('req_date', [$from, $to]);
            }
            $docExp = $doctorQuery->sum('fees') ?: 0;

            // Milk Income
            $milkQuery = MilkRecord::where('farmer_id', $farmer->id);
            if ($from && $to) {
                $milkQuery->whereBetween('milk_date', [$from, $to]);
            }
            $milkIncome = $milkQuery->sum('total_price') ?: 0;

            // Feed Expenses
            $feedQuery = DailyRecord::where('farmer_id', $farmer->id)
                ->where('type', 'feed');
            if ($from && $to) {
                $feedQuery->whereBetween('record_date', [$from, $to]);
            }
            $feedExp = $feedQuery->sum('amount') ?: 0;

            // Animal Sales
            $saleQuery = DailyRecord::where('farmer_id', $farmer->id)
                ->where('name', 'Animal Sell');
            if ($from && $to) {
                $saleQuery->whereBetween('record_date', [$from, $to]);
            }
            $sale = $saleQuery->sum('amount') ?: 0;

            // Animal Purchases
            $purchaseQuery = DailyRecord::where('farmer_id', $farmer->id)
                ->where('name', 'Animal Purchase');
            if ($from && $to) {
                $purchaseQuery->whereBetween('record_date', [$from, $to]);
            }
            $purchase = $purchaseQuery->sum('amount') ?: 0;

            // Pregnancy Care
            $pregnancyQuery = DailyRecord::where('farmer_id', $farmer->id)
                ->where('name', 'Pregnancy Care');
            if ($from && $to) {
                $pregnancyQuery->whereBetween('record_date', [$from, $to]);
            }
            $prgCare = $pregnancyQuery->sum('amount') ?: 0;

            // Profit
            $profitQuery = DailyRecord::where('farmer_id', $farmer->id)
                ->where('type', 'profit');
            if ($from && $to) {
                $profitQuery->whereBetween('record_date', [$from, $to]);
            }
            $profit = $profitQuery->sum('amount') ?: 0;

            // Expenses
            $expenseQuery = DailyRecord::where('farmer_id', $farmer->id)
                ->where('type', 'expense');
            if ($from && $to) {
                $expenseQuery->whereBetween('record_date', [$from, $to]);
            }
            $expense = $expenseQuery->sum('amount') ?: 0;

            // Breeding Expenses
            $breedingQuery = BreedingRecord::where('farmer_id', $farmer->id);
            if ($from && $to) {
                $breedingQuery->whereBetween('only_date', [$from, $to]);
            }
            $brExpense = $breedingQuery->sum('expenses') ?: 0;

            // Calculate Metrics
            $animalExpenses = $medicalExp + $docExp + $prgCare;
            $subProfit = $profit + $milkIncome;
            $subExpense = $expense + $feedExp + $animalExpenses;
            $profitLoss = $subProfit - $subExpense;

            $data = [
                'sale' => $sale,
                'purchase' => $purchase,
                'profit_loss' => $profitLoss,
                'feed_expenses' => $feedExp,
                'milk_income' => $milkIncome,
                'animal_expenses' => $animalExpenses,
                'breeding_expense' => $brExpense,
            ];

            Log::info('Reports: Report generated successfully', [
                'farmer_id' => $farmer->id,
                'from' => $from,
                'to' => $to,
                'data' => $data,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Reports: Database error', [
                'farmer_id' => $farmer->id ?? null,
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
            Log::error('Reports: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error generating report: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function diseaseInfo(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('DiseaseInfo: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('DiseaseInfo: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $diseases = Disease::where('is_active', 1)->get();

            $data = $diseases->map(function ($disease) {
                return [
                    'title' => $disease->title,
                    'description' => $disease->content,
                    'image' => $disease->image1 ? asset('storage/' . $disease->image1) : '',
                ];
            })->toArray();

            Log::info('DiseaseInfo: Disease data retrieved successfully', [
                'farmer_id' => $farmer->id,
                'disease_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('DiseaseInfo: Error retrieving disease data', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving disease data: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function stockHandling(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('StockHandling: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('StockHandling: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('StockHandling: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'stock_date' => 'required|date_format:Y-m-d',
                'green_forage' => 'nullable|numeric|min:0',
                'dry_fodder' => 'nullable|numeric|min:0',
                'silage' => 'nullable|numeric|min:0',
                'cake' => 'nullable|numeric|min:0',
                'grains' => 'nullable|numeric|min:0',
                'bioproducts' => 'nullable|numeric|min:0',
                'churi' => 'nullable|numeric|min:0',
                'oil_seeds' => 'nullable|numeric|min:0',
                'minerals' => 'nullable|numeric|min:0',
                'bypass_fat' => 'nullable|numeric|min:0',
                'toxins' => 'nullable|numeric|min:0',
                'buffer' => 'nullable|numeric|min:0',
                'yeast' => 'nullable|numeric|min:0',
                'calcium' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                Log::warning('StockHandling: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            $data = [
                'farmer_id' => $farmer->id,
                'stock_date' => $request->input('stock_date'),
                'green_forage' => $request->input('green_forage'),
                'dry_fodder' => $request->input('dry_fodder'),
                'silage' => $request->input('silage'),
                'cake' => $request->input('cake'),
                'grains' => $request->input('grains'),
                'bioproducts' => $request->input('bioproducts'),
                'churi' => $request->input('churi'),
                'oil_seeds' => $request->input('oil_seeds'),
                'minerals' => $request->input('minerals'),
                'bypass_fat' => $request->input('bypass_fat'),
                'toxins' => $request->input('toxins'),
                'buffer' => $request->input('buffer'),
                'yeast' => $request->input('yeast'),
                'calcium' => $request->input('calcium'),
                'date' => $currentDateTime,
            ];

            $stockHandling = StockHandling::create($data);

            Log::info('StockHandling: Record inserted successfully', [
                'farmer_id' => $farmer->id,
                'stock_handling_id' => $stockHandling->id,
                'stock_date' => $request->input('stock_date'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('StockHandling: Database error', [
                'farmer_id' => $farmer->id ?? null,
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
            Log::error('StockHandling: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewStocks(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');
            $pageIndex = $request->header('Index', 1); // Default to page 1 if not provided

            if (empty($authToken)) {
                Log::warning('ViewStocks: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('ViewStocks: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $limit = 20;
            $pageIndex = max(1, (int) $pageIndex); // Ensure pageIndex is at least 1
            $offset = ($pageIndex - 1) * $limit;

            // Count total records for pagination
            $count = StockHandling::where('farmer_id', $farmer->id)
                ->where('is_txn', 0)
                ->count();

            // Calculate total pages
            $totalPages = ceil($count / $limit);

            // Fetch stock handling records for the current page
            $stocks = StockHandling::where('farmer_id', $farmer->id)
                ->where('is_txn', 0)
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            // Calculate summary of totals
            $summary = StockHandling::where('farmer_id', $farmer->id)
                ->selectRaw('
                    COALESCE(SUM(green_forage), 0) as green_forage,
                    COALESCE(SUM(dry_fodder), 0) as dry_fodder,
                    COALESCE(SUM(silage), 0) as silage,
                    COALESCE(SUM(cake), 0) as cake,
                    COALESCE(SUM(grains), 0) as grains,
                    COALESCE(SUM(bioproducts), 0) as bioproducts,
                    COALESCE(SUM(churi), 0) as churi,
                    COALESCE(SUM(oil_seeds), 0) as oil_seeds,
                    COALESCE(SUM(minerals), 0) as minerals,
                    COALESCE(SUM(bypass_fat), 0) as bypass_fat,
                    COALESCE(SUM(toxins), 0) as toxins,
                    COALESCE(SUM(buffer), 0) as buffer,
                    COALESCE(SUM(yeast), 0) as yeast,
                    COALESCE(SUM(calcium), 0) as calcium
                ')
                ->first()
                ->toArray();

            // Prepare response data
            $data = $stocks->map(function ($stock) {
                return [
                    'stock_date' => $stock->stock_date,
                    'green_forage' => $stock->green_forage,
                    'dry_fodder' => $stock->dry_fodder,
                    'silage' => $stock->silage,
                    'cake' => $stock->cake,
                    'grains' => $stock->grains,
                    'bioproducts' => $stock->bioproducts,
                    'churi' => $stock->churi,
                    'oil_seeds' => $stock->oil_seeds,
                    'minerals' => $stock->minerals,
                    'bypass_fat' => $stock->bypass_fat,
                    'toxins' => $stock->toxins,
                    'buffer' => $stock->buffer,
                    'yeast' => $stock->yeast,
                    'calcium' => $stock->calcium,
                    'date' => Carbon::parse($stock->date)->format('d/m/Y'),
                ];
            })->toArray();

            // Generate pagination metadata
            $pagination = $this->createPagination($pageIndex, $totalPages);

            Log::info('ViewStocks: Records retrieved successfully', [
                'farmer_id' => $farmer->id,
                'page_index' => $pageIndex,
                'record_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'summary' => $summary,
                'pagination' => $pagination,
                'last' => $totalPages,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ViewStocks: Error retrieving records', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving records: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewStocksTxn(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');
            $pageIndex = $request->header('Index', 1); // Default to page 1 if not provided

            if (empty($authToken)) {
                Log::warning('ViewStocksTxn: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('ViewStocksTxn: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $limit = 20;
            $pageIndex = max(1, (int) $pageIndex); // Ensure pageIndex is at least 1
            $offset = ($pageIndex - 1) * $limit;

            // Count total records for pagination (is_txn = 1)
            $count = StockHandling::where('farmer_id', $farmer->id)
                ->where('is_txn', 1)
                ->count();

            // Calculate total pages
            $totalPages = ceil($count / $limit);

            // Fetch stock handling records for the current page
            $stocks = StockHandling::where('farmer_id', $farmer->id)
                ->where('is_txn', 1)
                ->orderBy('id', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            // Calculate summary of totals (all records, not filtered by is_txn)
            $summary = StockHandling::where('farmer_id', $farmer->id)
                ->selectRaw('
                    COALESCE(SUM(green_forage), 0) as green_forage,
                    COALESCE(SUM(dry_fodder), 0) as dry_fodder,
                    COALESCE(SUM(silage), 0) as silage,
                    COALESCE(SUM(cake), 0) as cake,
                    COALESCE(SUM(grains), 0) as grains,
                    COALESCE(SUM(bioproducts), 0) as bioproducts,
                    COALESCE(SUM(churi), 0) as churi,
                    COALESCE(SUM(oil_seeds), 0) as oil_seeds,
                    COALESCE(SUM(minerals), 0) as minerals,
                    COALESCE(SUM(bypass_fat), 0) as bypass_fat,
                    COALESCE(SUM(toxins), 0) as toxins,
                    COALESCE(SUM(buffer), 0) as buffer,
                    COALESCE(SUM(yeast), 0) as yeast,
                    COALESCE(SUM(calcium), 0) as calcium
                ')
                ->first()
                ->toArray();

            // Prepare response data
            $data = $stocks->map(function ($stock) {
                return [
                    'stock_date' => $stock->stock_date,
                    'green_forage' => $stock->green_forage,
                    'dry_fodder' => $stock->dry_fodder,
                    'silage' => $stock->silage,
                    'cake' => $stock->cake,
                    'grains' => $stock->grains,
                    'bioproducts' => $stock->bioproducts,
                    'churi' => $stock->churi,
                    'oil_seeds' => $stock->oil_seeds,
                    'minerals' => $stock->minerals,
                    'bypass_fat' => $stock->bypass_fat,
                    'toxins' => $stock->toxins,
                    'buffer' => $stock->buffer,
                    'yeast' => $stock->yeast,
                    'calcium' => $stock->calcium,
                    'date' => Carbon::parse($stock->date)->format('d/m/Y'),
                ];
            })->toArray();

            // Generate pagination metadata
            $pagination = $this->createPagination($pageIndex, $totalPages);

            Log::info('ViewStocksTxn: Transactional records retrieved successfully', [
                'farmer_id' => $farmer->id,
                'page_index' => $pageIndex,
                'record_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'summary' => $summary,
                'pagination' => $pagination,
                'last' => $totalPages,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ViewStocksTxn: Error retrieving records', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving records: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewSemenTank(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');

            if (empty($authToken)) {
                Log::warning('ViewSemenTank: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('ViewSemenTank: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $tanks = Tank::where('farmer_id', $farmer->id)->get();

            $data = $tanks->map(function ($tank, $index) use ($farmer) {
                $canisters = Canister::where('farmer_id', $farmer->id)
                    ->where('tank_id', $tank->id)
                    ->get()
                    ->toArray();

                return [
                    's_no' => $index + 1,
                    'id' => $tank->id,
                    'name' => $tank->name,
                    'canister' => $canisters,
                ];
            })->toArray();

            Log::info('ViewSemenTank: Tank data retrieved successfully', [
                'farmer_id' => $farmer->id,
                'tank_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ViewSemenTank: Error retrieving tank data', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving tank data: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function addSemenTank(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('AddSemenTank: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('AddSemenTank: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('AddSemenTank: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('AddSemenTank: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $name = $request->input('name');

            // Check for duplicate tank name
            $existingTank = Tank::where('farmer_id', $farmer->id)
                ->where('name', $name)
                ->first();

            if ($existingTank) {
                Log::warning('AddSemenTank: Tank name already exists', [
                    'farmer_id' => $farmer->id,
                    'tank_name' => $name,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Tank name already exist!',
                    'status' => 201,
                ], 422);
            }

            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            // Create tank
            $tank = Tank::create([
                'farmer_id' => $farmer->id,
                'name' => $name,
                'date' => $currentDateTime,
            ]);

            // Create 6 canisters
            for ($i = 0; $i < 6; $i++) {
                Canister::create([
                    'farmer_id' => $farmer->id,
                    'tank_id' => $tank->id,
                    'date' => $currentDateTime,
                ]);
            }

            Log::info('AddSemenTank: Tank and canisters inserted successfully', [
                'farmer_id' => $farmer->id,
                'tank_id' => $tank->id,
                'tank_name' => $name,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('AddSemenTank: Database error', [
                'farmer_id' => $farmer->id ?? null,
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
            Log::error('AddSemenTank: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function deleteSemenTank(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('DeleteSemenTank: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('DeleteSemenTank: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('DeleteSemenTank: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:tbl_tank,id,farmer_id,' . $farmer->id,
            ]);

            if ($validator->fails()) {
                Log::warning('DeleteSemenTank: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $tankId = $request->input('id');

            // Perform deletion in a transaction
            $success = DB::transaction(function () use ($farmer, $tankId) {
                $deletedCanisters = Canister::where('farmer_id', $farmer->id)
                    ->where('tank_id', $tankId)
                    ->delete();

                $deletedTank = Tank::where('farmer_id', $farmer->id)
                    ->where('id', $tankId)
                    ->delete();

                // Note: $deletedTank and $deletedCanisters return the number of affected rows
                // Return true if tank was deleted (canisters may be 0 if none exist)
                return $deletedTank > 0;
            });

            if ($success) {
                Log::info('DeleteSemenTank: Tank and canisters deleted successfully', [
                    'farmer_id' => $farmer->id,
                    'tank_id' => $tankId,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Tank Successfully Deleted!',
                    'status' => 200,
                ], 200);
            } else {
                Log::warning('DeleteSemenTank: Deletion failed', [
                    'farmer_id' => $farmer->id,
                    'tank_id' => $tankId,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 422);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DeleteSemenTank: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'tank_id' => $tankId ?? null,
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
            Log::error('DeleteSemenTank: General error', [
                'farmer_id' => $farmer->id ?? null,
                'tank_id' => $tankId ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function updateCanister(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('UpdateCanister: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('UpdateCanister: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('UpdateCanister: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'canister_id' => 'required|numeric|exists:tbl_canister,id,farmer_id,' . $farmer->id,
                'farm_bull' => 'nullable|string|max:255',
                'tag_no' => 'nullable|string|max:255',
                'bull_name' => 'nullable|string|max:255|unique:tbl_canister,bull_name,' . $request->input('canister_id'),
                'company_name' => 'nullable|string|max:255',
                'no_of_units' => 'nullable|numeric|min:0',
                'milk_production_of_mother' => 'nullable|numeric|min:0',
            ]);

            if ($validator->fails()) {
                Log::warning('UpdateCanister: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $canisterId = $request->input('canister_id');
            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            $updateData = array_filter([
                'farm_bull' => $request->input('farm_bull'),
                'tag_no' => $request->input('tag_no'),
                'bull_name' => $request->input('bull_name'),
                'company_name' => $request->input('company_name'),
                'no_of_units' => $request->input('no_of_units'),
                'milk_production_of_mother' => $request->input('milk_production_of_mother'),
                'date' => $currentDateTime,
            ], function ($value) {
                return !is_null($value);
            });

            $updated = Canister::where('id', $canisterId)
                ->where('farmer_id', $farmer->id)
                ->update($updateData);

            if ($updated) {
                Log::info('UpdateCanister: Canister updated successfully', [
                    'farmer_id' => $farmer->id,
                    'canister_id' => $canisterId,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Record Successfully Updated!',
                    'status' => 200,
                ], 200);
            } else {
                Log::warning('UpdateCanister: Canister not found or no changes made', [
                    'farmer_id' => $farmer->id,
                    'canister_id' => $canisterId,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 422);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('UpdateCanister: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'canister_id' => $canisterId ?? null,
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
            Log::error('UpdateCanister: General error', [
                'farmer_id' => $farmer->id ?? null,
                'canister_id' => $canisterId ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function farmSummary(Request $request)
    {
        try {
            $authToken = $request->header('Authentication');

            if (empty($authToken)) {
                Log::warning('FarmSummary: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('FarmSummary: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $openCount = MyAnimal::where('farmer_id', $farmer->id)
                ->whereNull('delivered_date')
                ->count();

            $inseminateCount = MyAnimal::where('farmer_id', $farmer->id)
                ->where('is_inseminated', 'Yes')
                ->count();

            $pregnantCount = MyAnimal::where('farmer_id', $farmer->id)
                ->where('is_pregnant', 'Yes')
                ->count();

            $notPregnantCount = MyAnimal::where('farmer_id', $farmer->id)
                ->where('is_pregnant', 'No')
                ->count();

            $dryCount = MyAnimal::where('farmer_id', $farmer->id)
                ->whereNotNull('dry_date')
                ->count();

            $milkingCount = MyAnimal::where('farmer_id', $farmer->id)
                ->where('animal_type', 'Milking')
                ->count();

            $calfCount = MyAnimal::where('farmer_id', $farmer->id)
                ->where('animal_type', 'Calf')
                ->count();

            $bullCount = MyAnimal::where('farmer_id', $farmer->id)
                ->where('animal_type', 'Bull')
                ->count();

            $heiferCount = MyAnimal::where('farmer_id', $farmer->id)
                ->where('animal_type', 'Heifer')
                ->count();

            $data = [
                'open' => $openCount,
                'inseminate' => $inseminateCount,
                'pregnant' => $pregnantCount,
                'not_pregnant' => $notPregnantCount,
                'dry' => $dryCount,
                'milking' => $milkingCount,
                'calves' => $calfCount,
                'bull' => $bullCount,
                'heifers' => $heiferCount,
                'repeater' => 0,
            ];

            Log::info('FarmSummary: Summary retrieved successfully', [
                'farmer_id' => $farmer->id,
                'data' => $data,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('FarmSummary: Error retrieving summary', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving summary: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function getAnimals(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('GetAnimals: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('GetAnimals: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('GetAnimals: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'animal_type' => 'nullable|string|in:Bull,Heifer,Milking,Calf',
                'other' => 'nullable|string|in:inseminate,pregnant,not_pregnant,Open,Dry,repeater',
                'group_id' => 'nullable|string|regex:/^(\d+|none)$/',
            ]);
 
            if ($validator->fails()) {
                Log::warning('GetAnimals: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $animalType = $request->input('animal_type');
            $other = $request->input('other');
            $groupId = $request->input('group_id');

            $query = MyAnimal::with('group')
                ->where('farmer_id', $farmer->id)
                ->orderBy('id', 'desc');

            if (!empty($animalType)) {
                $query->where('animal_type', $animalType);
            }

            if (!empty($groupId) && $groupId !== 'none') {
                $query->where('assign_to_group', $groupId);
            }

            if (!empty($other)) {
                if ($other === 'inseminate') {
                    $query->where('is_inseminated', 'Yes');
                } elseif ($other === 'pregnant') {
                    $query->where('is_pregnant', 'Yes');
                } elseif ($other === 'not_pregnant') {
                    $query->where('is_pregnant', 'No');
                } elseif ($other === 'Open') {
                    $query->whereNull('delivered_date');
                } elseif ($other === 'Dry') {
                    $query->whereNotNull('dry_date');
                } elseif ($other === 'repeater') {
                    $query->where('id', 0);
                }
            }

            $animals = $query->get();

            $data = [];
            $groups = [['value' => 'none', 'label' => 'None']];
            $groupLabels = ['None'];

            foreach ($animals as $animal) {
                $groupName = $animal->group ? $animal->group->name : 'None';
                if ($animal->group && !in_array($groupName, $groupLabels)) {
                    $groups[] = [
                        'value' => $animal->group->id,
                        'label' => $groupName,
                    ];
                    $groupLabels[] = $groupName;
                }

                $data[] = [
                    'id' => $animal->id,
                    'animal_type' => $animal->animal_type,
                    'assign_to_group' => $groupName,
                    'animal_name' => $animal->animal_name,
                    'tag_no' => $animal->tag_no,
                    'dob' => $animal->dob ? Carbon::parse($animal->dob)->format('Y-m-d') : null,
                    'father_name' => $animal->father_name,
                    'mother_name' => $animal->mother_name,
                    'weight' => $animal->weight,
                    'age' => $animal->age,
                    'breed_type' => $animal->breed_type,
                    'semen_brand' => $animal->semen_brand,
                    'animal_gender' => $animal->animal_gender,
                    'is_inseminated' => $animal->is_inseminated,
                    'insemination_type' => $animal->insemination_type,
                    'insemination_date' => $animal->insemination_date ? Carbon::parse($animal->insemination_date)->format('Y-m-d H:i:s') : '',
                    'is_pregnant' => $animal->is_pregnant,
                    'pregnancy_test_date' => $animal->pregnancy_test_date ? Carbon::parse($animal->pregnancy_test_date)->format('Y-m-d H:i:s') : null,
                    'service_status' => $animal->service_status,
                    'in_house' => $animal->in_house,
                    'lactation' => $animal->lactation,
                    'calving_date' => $animal->calving_date ? Carbon::parse($animal->calving_date)->format('Y-m-d H:i:s') : null,
                    'insured_value' => $animal->insured_value,
                    'insurance_no' => $animal->insurance_no,
                    'renewal_period' => $animal->renewal_period,
                    'insurance_date' => $animal->insurance_date ? Carbon::parse($animal->insurance_date)->format('Y-m-d H:i:s') : null,
                    'dry_date' => $animal->dry_date ? Carbon::parse($animal->dry_date)->format('Y-m-d H:i:s') : null,
                    'delivered_date' => $animal->delivered_date ? Carbon::parse($animal->delivered_date)->format('Y-m-d H:i:s') : null,
                    'date' => $animal->created_at ? Carbon::parse($animal->created_at)->format('d/m/Y') : null,
                ];
            }

            Log::info('GetAnimals: Animals retrieved successfully', [
                'farmer_id' => $farmer->id,
                'animal_count' => count($data),
                'filters' => [
                    'animal_type' => $animalType,
                    'other' => $other,
                    'group_id' => $groupId,
                ],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'groups' => $groups,
                'group_id' => $groupId,
            ], 200);
        } catch (\Exception $e) {
            Log::error('GetAnimals: Error retrieving animals', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error retrieving animals: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function updateAnimalStatus(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('UpdateAnimalStatus: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('UpdateAnimalStatus: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('UpdateAnimalStatus: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|exists:tbl_my_animal,id,farmer_id,' . $farmer->id,
                'status' => 'required|string|in:Dry,Delivered,Pregnant,Not Pregnant',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                Log::warning('UpdateAnimalStatus: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $animalId = $request->input('id');
            $status = $request->input('status');
            $recordDate = $request->input('date');
            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            // Prepare animal cycle record
            $cycleData = [
                'record_date' => $recordDate,
                'farmer_id' => $farmer->id,
                'animal_id' => $animalId,
                'status' => $status,
                'date' => $currentDateTime,
            ];

            // Prepare animal update based on status
            $updateData = [];
            if ($status === 'Dry') {
                $updateData = ['dry_date' => $recordDate];
            } elseif ($status === 'Delivered') {
                $updateData = [
                    'delivered_date' => $recordDate,
                    'is_pregnant' => 'No',
                    'pregnancy_test_date' => null,
                    'dry_date' => null,
                ];
            } elseif ($status === 'Pregnant') {
                $updateData = [
                    'delivered_date' => null,
                    'is_pregnant' => 'Yes',
                    'pregnancy_test_date' => $recordDate,
                    'dry_date' => null,
                ];
            } elseif ($status === 'Not Pregnant') {
                $updateData = [
                    'delivered_date' => null,
                    'is_pregnant' => 'No',
                    'pregnancy_test_date' => null,
                    'dry_date' => null,
                    'calving_date' => $recordDate,
                ];
            }

            // Perform updates in a transaction
            DB::transaction(function () use ($cycleData, $updateData, $animalId, $farmer) {
                AnimalCycle::create($cycleData);
                MyAnimal::where('id', $animalId)
                    ->where('farmer_id', $farmer->id)
                    ->update($updateData);
            });

            Log::info('UpdateAnimalStatus: Animal status updated successfully', [
                'farmer_id' => $farmer->id,
                'animal_id' => $animalId,
                'status' => $status,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Record Successfully Updated!',
                'status' => 200,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('UpdateAnimalStatus: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'animal_id' => $animalId ?? null,
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
            Log::error('UpdateAnimalStatus: General error', [
                'farmer_id' => $farmer->id ?? null,
                'animal_id' => $animalId ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function salePurchaseUpdate(Request $request)
    {
        try {
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('SalePurchaseUpdate: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $authToken = $request->header('Authentication');
            if (empty($authToken)) {
                Log::warning('SalePurchaseUpdate: Missing Authentication header', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Authentication token is required',
                    'status' => 201,
                ], 403);
            }

            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('SalePurchaseUpdate: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => substr($authToken, 0, 10) . '...',
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request, $farmer) {
                        $type = $request->input('type');
                        if ($type === 'animal') {
                            if (!SalePurchase::where('id', $value)->where('farmer_id', $farmer->id)->exists()) {
                                $fail('The selected id is invalid for animal sale/purchase.');
                            }
                        } elseif ($type === 'equipment') {
                            if (!EquipmentSalePurchase::where('id', $value)->where('farmer_id', $farmer->id)->exists()) {
                                $fail('The selected id is invalid for equipment sale/purchase.');
                            }
                        }
                    },
                ],
                'type' => 'required|string|in:animal,equipment',
            ]);

            if ($validator->fails()) {
                Log::warning('SalePurchaseUpdate: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $id = $request->input('id');
            $type = $request->input('type');
            $updateData = ['status' => 2];

            if ($type === 'animal') {
                SalePurchase::where('id', $id)
                    ->where('farmer_id', $farmer->id)
                    ->update($updateData);
            } elseif ($type === 'equipment') {
                EquipmentSalePurchase::where('id', $id)
                    ->where('farmer_id', $farmer->id)
                    ->update($updateData);
            }

            Log::info('SalePurchaseUpdate: Record updated successfully', [
                'farmer_id' => $farmer->id,
                'type' => $type,
                'record_id' => $id,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
            ], 200);
        } catch (\Exception $e) {
            Log::error('SalePurchaseUpdate: Error updating record', [
                'farmer_id' => $farmer->id ?? null,
                'type' => $type ?? null,
                'record_id' => $id ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error updating record: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
}
