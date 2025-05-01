<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\MyAnimal;
use App\Models\BreedingRecord;
use App\Models\Canister;
use App\Models\Group;
use App\Models\HealthInfo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
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

    public function breedingRecord(Request $request)
    {
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('BreedingRecord auth attempt', [
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
                'group_id' => 'required|string',
                'cattle_type' => 'required|string',
                'tag_no' => 'required|string',
                'breeding_date' => 'required|date',
                'weight' => 'required|string',
                'date_of_ai' => 'required|date',
                'farm_bull' => 'required|string|in:Yes,No',
                'bull_tag_no' => 'nullable|string',
                'bull_name' => 'nullable|string',
                'expenses' => 'required|string',
                'vet_name' => 'required|string',
                'update_bull_semen' => 'required|string|in:Yes,No',
                'is_pregnant' => 'nullable|string',
                'pregnancy_test_date' => 'nullable|date',
                'semen_bull_id' => 'nullable|string',
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
                'group_id' => $request->group_id,
                'cattle_type' => $request->cattle_type,
                'tag_no' => $request->tag_no,
                'breeding_date' => $request->breeding_date,
                'weight' => $request->weight,
                'date_of_ai' => $request->date_of_ai,
                'farm_bull' => $request->farm_bull,
                'bull_tag_no' => $request->bull_tag_no,
                'bull_name' => $request->bull_name,
                'expenses' => $request->expenses,
                'vet_name' => $request->vet_name,
                'update_bull_semen' => $request->update_bull_semen,
                'semen_bull_id' => $request->semen_bull_id,
                'is_pregnant' => $request->is_pregnant,
                'pregnancy_test_date' => $request->pregnancy_test_date,
                'date' => now(),
                'only_date' => now()->format('Y-m-d'),
            ];

            // Insert into tbl_breeding_record
            $breedingRecord = BreedingRecord::create($data);

            // Update canister if update_bull_semen is Yes
            if ($request->update_bull_semen === 'Yes') {
                if ($request->farm_bull === 'Yes') {
                    $canister = Canister::where('farmer_id', $user->id)
                        ->where('tag_no', $request->bull_tag_no)
                        ->first();
                    if ($canister) {
                        $canister->update(['no_of_units' => $canister->no_of_units - 1]);
                        Log::info('Canister updated (farm_bull)', [
                            'canister_id' => $canister->id,
                            'farmer_id' => $user->id,
                            'tag_no' => $request->bull_tag_no,
                            'new_units' => $canister->no_of_units,
                        ]);
                    } else {
                        Log::warning('Canister not found (farm_bull)', [
                            'farmer_id' => $user->id,
                            'tag_no' => $request->bull_tag_no,
                        ]);
                    }
                } else {
                    $canister = Canister::where('farmer_id', $user->id)
                        ->where('id', $request->semen_bull_id)
                        ->first();
                    if ($canister) {
                        $canister->update(['no_of_units' => $canister->no_of_units - 1]);
                        Log::info('Canister updated (semen_bull)', [
                            'canister_id' => $canister->id,
                            'farmer_id' => $user->id,
                            'semen_bull_id' => $request->semen_bull_id,
                            'new_units' => $canister->no_of_units,
                        ]);
                    } else {
                        Log::warning('Canister not found (semen_bull)', [
                            'farmer_id' => $user->id,
                            'semen_bull_id' => $request->semen_bull_id,
                        ]);
                    }
                }
            }

            Log::info('Breeding record inserted', [
                'farmer_id' => $user->id,
                'breeding_record_id' => $breedingRecord->id,
                'cattle_type' => $request->cattle_type,
                'tag_no' => $request->tag_no,
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
                'data' => [],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in breedingRecord', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error inserting breeding record: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewBreedingRecord(Request $request)
    {
        try {
            // Authenticate user using 'farmer' guard
            $user = auth('farmer')->user();
            Log::info('ViewBreedingRecord auth attempt', [
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

            // Fetch paginated breeding records
            $breedData = BreedingRecord::where('farmer_id', $user->id)
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageIndex);

            // Prepare response data
            $data = [];
            $i = ($breedData->currentPage() - 1) * $perPage + 1;

            foreach ($breedData as $breed) {
                // Fetch group name if group_id exists
                $group = '';
                if ($breed->group_id) {
                    $groupData = Group::find($breed->group_id);
                    $group = $groupData ? $groupData->name : '';
                }

                // Fetch bull name based on farm_bull
                $bull_name = '';
                if ($breed->farm_bull === 'Yes') {
                    $bullData = MyAnimal::where('tag_no', $breed->tag_no)->first();
                    $bull_name = $bullData ? $bullData->animal_name : '';
                } else {
                    $bullData = Canister::find($breed->semen_bull_id);
                    $bull_name = $bullData ? ($bullData->bull_name ?? '') : '';
                }

                $data[] = [
                    's_no' => $i,
                    'group' => $group,
                    'cattle_type' => $breed->cattle_type,
                    'tag_no' => $breed->tag_no,
                    'breeding_date' => $breed->breeding_date,
                    'weight' => $breed->weight,
                    'date_of_ai' => $breed->date_of_ai,
                    'farm_bull' => $breed->farm_bull,
                    'bull_tag_no' => $breed->bull_tag_no,
                    'bull_name' => $bull_name,
                    'expenses' => $breed->expenses,
                    'vet_name' => $breed->vet_name,
                    'is_pregnant' => $breed->is_pregnant,
                    'pregnancy_test_date' => $breed->pregnancy_test_date,
                    'date' => $breed->date ? $breed->date->format('d/m/Y') : '',
                ];
                $i++;
            }

            Log::info('ViewBreedingRecord retrieved', [
                'farmer_id' => $user->id,
                'page' => $pageIndex,
                'total_records' => $breedData->total(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'pagination' => [
                    'current_page' => $breedData->currentPage(),
                    'last_page' => $breedData->lastPage(),
                    'per_page' => $breedData->perPage(),
                    'total' => $breedData->total(),
                    'from' => $breedData->firstItem(),
                    'to' => $breedData->lastItem(),
                ],
                'last' => $breedData->lastPage(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in viewBreedingRecord', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error retrieving breeding records: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
}
