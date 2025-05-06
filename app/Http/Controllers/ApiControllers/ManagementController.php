<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\DailyRecord;
use App\Models\StockHandling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ManagementController extends Controller
{
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
                'date' => 'required|date_format:Y-m-d',
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
}
