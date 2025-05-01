<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\Group;
use App\Models\HealthInfo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
class BreedController extends Controller
{
    public function healthInfo(Request $request)
    {
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('HealthInfo auth attempt', [
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
                'information_type' => 'required|string',
                'group_id' => 'nullable|string',
                'cattle_type' => 'nullable|string',
                'tag_no' => 'nullable|string',
                'vaccination_date' => 'required|date',
                'disease_name' => 'nullable|string',
                'vaccination' => 'nullable|string',
                'medicine' => 'nullable|string',
                'deworming' => 'nullable|string',
                'other1' => 'nullable|string',
                'other2' => 'nullable|string',
                'other3' => 'nullable|string',
                'other4' => 'nullable|string',
                'other5' => 'nullable|string',
                'milk_loss' => 'nullable|string',
                'treatment_cost' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Prepare data for insertion
            $data = [
                'farmer_id' => $user->id,
                'information_type' => $request->information_type,
                'group_id' => $request->group_id,
                'cattle_type' => $request->cattle_type,
                'tag_no' => $request->tag_no,
                'vaccination_date' => $request->vaccination_date,
                'disease_name' => $request->disease_name,
                'vaccination' => $request->vaccination,
                'medicine' => $request->medicine,
                'deworming' => $request->deworming,
                'other1' => $request->other1,
                'other2' => $request->other2,
                'other3' => $request->other3,
                'other4' => $request->other4,
                'other5' => $request->other5,
                'milk_loss' => $request->milk_loss,
                'treatment_cost' => $request->treatment_cost,
                'date' => now(),
            ];

            // Insert into tbl_health_info
            $healthInfo = HealthInfo::create($data);

            Log::info('Health info inserted', [
                'farmer_id' => $user->id,
                'health_info_id' => $healthInfo->id,
                'information_type' => $request->information_type,
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
                'data' => [],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in healthInfo', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error inserting health info: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
    
    public function viewHealthInfo(Request $request)
    {
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('ViewHealthInfo auth attempt', [
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

            // Get page index from headers (default to 1)
            $pageIndex = $request->header('Index', 1);
            $perPage = 20;

            // Fetch paginated health info
            $healthData = HealthInfo::where('farmer_id', $user->id)
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageIndex);

            // Prepare response data
            $data = [];
            $i = ($healthData->currentPage() - 1) * $perPage + 1;

            foreach ($healthData as $health) {
                // Fetch group name if group_id exists
                $group = '';
                if ($health->group_id) {
                    $groupData = Group::find($health->group_id);
                    $group = $groupData ? $groupData->name : '';
                }

                $data[] = [
                    's_no' => $i,
                    'information_type' => $health->information_type,
                    'group' => $group,
                    'cattle_type' => $health->cattle_type,
                    'tag_no' => $health->tag_no,
                    'vaccination_date' => $health->vaccination_date,
                    'dieses_name' => $health->disease_name, // Corrected typo
                    'vaccination' => $health->vaccination,
                    'medicine' => $health->medicine,
                    'deworming' => $health->deworming,
                    'other1' => $health->other1,
                    'other2' => $health->other2,
                    'other3' => $health->other3,
                    'other4' => $health->other4,
                    'other5' => $health->other5,
                    'milk_loss' => $health->milk_loss,
                    'treatment_cost' => $health->treatment_cost,
                    'date' => $health->date ? $health->date->format('d/m/Y') : '',
                ];
                $i++;
            }

            Log::info('ViewHealthInfo retrieved', [
                'farmer_id' => $user->id,
                'page' => $pageIndex,
                'total_records' => $healthData->total(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'pagination' => [
                    'current_page' => $healthData->currentPage(),
                    'last_page' => $healthData->lastPage(),
                    'per_page' => $healthData->perPage(),
                    'total' => $healthData->total(),
                    'from' => $healthData->firstItem(),
                    'to' => $healthData->lastItem(),
                ],
                'last' => $healthData->lastPage(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in viewHealthInfo', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error retrieving health info: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
}
