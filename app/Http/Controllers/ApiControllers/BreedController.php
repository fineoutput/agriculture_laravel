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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
class BreedController extends Controller
{
    public function healthInfo(Request $request)
    {
        Log::info('healthInfo request', [
            'information_type' => $request->input('information_type'),
            'group_id' => $request->input('group_id'),
            'cattle_type' => $request->input('cattle_type'),
            'tag_no' => $request->input('tag_no'),
            'vaccination_date' => $request->input('vaccination_date'),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        // Validate inputs
        $token = $request->header('Authentication');
        $validator = Validator::make(array_merge($request->all(), ['Authentication' => $token]), [
            'information_type' => 'required|string',
            'group_id' => 'nullable|integer|exists:tbl_group,id',
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
            // 'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
            'group_id.exists' => 'Invalid group ID',
        ]);

        if ($validator->fails()) {
            Log::warning('healthInfo: Validation failed', [
                'errors' => $validator->errors(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::debug('healthInfo: Farmer query result', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'farmer_found' => $farmer ? true : false,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('healthInfo: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Verify group belongs to farmer if provided
            if ($request->filled('group_id')) {
                $group = Group::where('id', $request->input('group_id'))
                    ->where('farmer_id', $farmer->id)
                    ->where('is_active', 1)
                    ->first();

                if (!$group) {
                    Log::warning('healthInfo: Group not found or unauthorized', [
                        'group_id' => $request->input('group_id'),
                        'farmer_id' => $farmer->id,
                        'ip' => $request->ip(),
                    ]);
                    return response()->json([
                        'message' => 'Invalid or unauthorized group!',
                        'status' => 201,
                    ], 403);
                }
            }

            // Prepare data for insertion
            $data = [
                'farmer_id' => $farmer->id,
                'information_type' => $request->input('information_type'),
                'group_id' => $request->input('group_id'),
                'cattle_type' => $request->input('cattle_type'),
                'tag_no' => $request->input('tag_no'),
                'vaccination_date' => $request->input('vaccination_date'),
                'disease_name' => $request->input('disease_name'),
                'vaccination' => $request->input('vaccination'),
                'medicine' => $request->input('medicine'),
                'deworming' => $request->input('deworming'),
                'other1' => $request->input('other1'),
                'other2' => $request->input('other2'),
                'other3' => $request->input('other3'),
                'other4' => $request->input('other4'),
                'other5' => $request->input('other5'),
                'milk_loss' => $request->input('milk_loss'),
                'treatment_cost' => $request->input('treatment_cost'),
                'date' => now()->setTimezone('Asia/Kolkata'),
            ];

            // Insert into tbl_health_info
            $healthInfo = HealthInfo::insert($data);

            Log::info('healthInfo: Health info inserted', [
                'farmer_id' => $farmer->id,
                'health_info_id' => $healthInfo->id,
                'information_type' => $request->input('information_type'),
                'group_id' => $request->input('group_id'),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Record Successfully Inserted!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('healthInfo: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'information_type' => $request->input('information_type'),
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
            Log::error('healthInfo: General error', [
                'farmer_id' => $farmer->id ?? null,
                'information_type' => $request->input('information_type'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error inserting health info: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
    
    public function viewHealthInfo(Request $request)
    {
        Log::info('viewHealthInfo request', [
            'page_index' => $request->header('Index', 1),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        // Validate authentication header
        $token = $request->header('Authentication');
        $validator = Validator::make(['Authentication' => $token], [
            'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
        ]);

        if ($validator->fails()) {
            Log::warning('viewHealthInfo: Validation failed', [
                'errors' => $validator->errors(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::debug('viewHealthInfo: Farmer query result', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'farmer_found' => $farmer ? true : false,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('viewHealthInfo: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Get page index from headers (default to 1)
            $pageIndex = $request->header('Index', 1);
            $perPage = 20;

            // Fetch paginated health info
            $healthData = HealthInfo::where('farmer_id', $farmer->id)
                ->orderBy('id', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageIndex);

            // Prepare response data
            $data = [];
            $i = ($healthData->currentPage() - 1) * $perPage + 1;

            foreach ($healthData as $health) {
                // Fetch group name if group_id exists
                $group = '';
                if ($health->group_id) {
                    $groupData = Group::where('id', $health->group_id)
                        ->where('farmer_id', $farmer->id)
                        ->where('is_active', 1)
                        ->first();
                    $group = $groupData ? $groupData->name : '';
                }

                $data[] = [
                    's_no' => $i,
                    'information_type' => $health->information_type,
                    'group' => $group,
                    'cattle_type' => $health->cattle_type,
                    'tag_no' => $health->tag_no,
                    'vaccination_date' => $health->vaccination_date,
                    'disease_name' => $health->disease_name,
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

            Log::info('viewHealthInfo: Health info retrieved', [
                'farmer_id' => $farmer->id,
                'page' => $pageIndex,
                'total_records' => $healthData->total(),
                'ip' => $request->ip(),
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
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('viewHealthInfo: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'page_index' => $pageIndex,
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
            Log::error('viewHealthInfo: General error', [
                'farmer_id' => $farmer->id ?? null,
                'page_index' => $pageIndex,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
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

      public function myAnimal(Request $request)
    {
        Log::info('myAnimal request', [
            'animal_type' => $request->input('animal_type'),
            'tag_no' => $request->input('tag_no'),
            'assign_to_group' => $request->input('assign_to_group'),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        // Validate inputs
        $token = $request->header('Authentication');
        $validator = Validator::make(array_merge($request->all(), ['Authentication' => $token]), [
            'animal_type' => 'required|string|in:Milking,Calf,Heifer,Bull',
            'assign_to_group' => 'required|integer|exists:tbl_group,id',
            'tag_no' => 'required|string|max:255',
            'animal_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric',
            'age' => 'nullable|numeric',
            'breed_type' => 'nullable|string|max:255',
            'semen_brand' => 'nullable|string|max:255',
            'insemination_date' => 'nullable|date',
            'is_pregnant' => 'nullable|in:0,1',
            'pregnancy_test_date' => 'nullable|date',
            'animal_gender' => 'nullable|string|in:Male,Female',
            'is_inseminated' => 'nullable|in:0,1',
            'insemination_type' => 'nullable|string|max:255',
            'service_status' => 'nullable|string|max:255',
            'in_house' => 'nullable|in:0,1',
            'lactation' => 'nullable|numeric',
            'calving_date' => 'nullable|date',
            'insured_value' => 'nullable|numeric',
            'insurance_no' => 'nullable|string|max:255',
            'renewal_period' => 'nullable|string|max:255',
            'insurance_date' => 'nullable|date',
            'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
            'assign_to_group.exists' => 'Invalid group ID',
            'animal_type.in' => 'Invalid animal type. Must be Milking, Calf, Heifer, or Bull',
        ]);

        if ($validator->fails()) {
            Log::warning('myAnimal: Validation failed', [
                'errors' => $validator->errors(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 201,
            ], 422);
        }

        try {
            // Authenticate farmer by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            Log::debug('myAnimal: Farmer query result', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'farmer_found' => $farmer ? true : false,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('myAnimal: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Verify group belongs to farmer
            $group = Group::where('id', $request->input('assign_to_group'))
                ->where('farmer_id', $farmer->id)
                ->where('is_active', 1)
                ->first();

            if (!$group) {
                Log::warning('myAnimal: Group not found or unauthorized', [
                    'group_id' => $request->input('assign_to_group'),
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Invalid or unauthorized group!',
                    'status' => 201,
                ], 403);
            }

            // Check if tag_no is unique for this farmer
            $existingAnimal = MyAnimal::where('farmer_id', $farmer->id)
                ->where('tag_no', $request->input('tag_no'))
                ->exists();

            if ($existingAnimal) {
                Log::warning('myAnimal: Duplicate tag_no', [
                    'tag_no' => $request->input('tag_no'),
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Tag No Must Be Different!',
                    'status' => 201,
                ], 422);
            }

            // Prepare data based on animal_type
            $animalType = $request->input('animal_type');
            $commonData = [
                'farmer_id' => $farmer->id,
                'animal_type' => $animalType,
                'assign_to_group' => $request->input('assign_to_group'),
                'animal_name' => $request->input('animal_name'),
                'tag_no' => $request->input('tag_no'),
                'date' => now()->setTimezone('Asia/Kolkata'),
            ];

            $typeSpecificData = [];

            switch ($animalType) {
                case 'Milking':
                    $typeSpecificData = [
                        'dob' => $request->input('dob'),
                        'father_name' => $request->input('father_name'),
                        'mother_name' => $request->input('mother_name'),
                        'weight' => $request->input('weight'),
                        'age' => $request->input('age'),
                        'breed_type' => $request->input('breed_type'),
                        'semen_brand' => $request->input('semen_brand'),
                        'insemination_date' => $request->input('insemination_date'),
                        'pregnancy_test_date' => $request->input('pregnancy_test_date'),
                        'animal_gender' => $request->input('animal_gender'),
                        'is_inseminated' => $request->input('is_inseminated'),
                        'insemination_type' => $request->input('insemination_type'),
                        'is_pregnant' => $request->input('is_pregnant'),
                        'service_status' => $request->input('service_status'),
                        'in_house' => $request->input('in_house'),
                        'lactation' => $request->input('lactation'),
                        'calving_date' => $request->input('calving_date'),
                        'insured_value' => $request->input('insured_value'),
                        'insurance_no' => $request->input('insurance_no'),
                        'renewal_period' => $request->input('renewal_period'),
                        'insurance_date' => $request->input('insurance_date'),
                    ];
                    break;

                case 'Calf':
                    $typeSpecificData = [
                        'dob' => $request->input('dob'),
                        'father_name' => $request->input('father_name'),
                        'mother_name' => $request->input('mother_name'),
                        'weight' => $request->input('weight'),
                        'age' => $request->input('age'),
                    ];
                    break;

                case 'Heifer':
                    $typeSpecificData = [
                        'dob' => $request->input('dob'),
                        'breed_type' => $request->input('breed_type'),
                        'is_inseminated' => $request->input('is_inseminated'),
                        'insemination_type' => $request->input('insemination_type'),
                        'semen_brand' => $request->input('semen_brand'),
                        'insemination_date' => $request->input('insemination_date'),
                        'is_pregnant' => $request->input('is_pregnant'),
                        'pregnancy_test_date' => $request->input('pregnancy_test_date'),
                        'animal_gender' => $request->input('animal_gender'),
                    ];
                    break;

                case 'Bull':
                    $typeSpecificData = [
                        'dob' => $request->input('dob'),
                        'father_name' => $request->input('father_name'),
                        'mother_name' => $request->input('mother_name'),
                        'weight' => $request->input('weight'),
                        'age' => $request->input('age'),
                        'in_house' => $request->input('in_house'),
                        'service_status' => $request->input('service_status'),
                    ];
                    break;

                default:
                    Log::warning('myAnimal: Invalid animal_type', [
                        'animal_type' => $animalType,
                        'farmer_id' => $farmer->id,
                        'ip' => $request->ip(),
                    ]);
                    return response()->json([
                        'message' => 'Invalid animal type!',
                        'status' => 201,
                    ], 422);
            }

            // Insert animal
            $data = array_merge($commonData, $typeSpecificData);
            $animal = DB::transaction(function () use ($data) {
                return MyAnimal::create($data);
            });

            Log::info('myAnimal: Animal registered successfully', [
                'farmer_id' => $farmer->id,
                'animal_id' => $animal->id,
                'tag_no' => $animal->tag_no,
                'animal_type' => $animal->animal_type,
                'group_id' => $animal->assign_to_group,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Animal Successfully Registered!',
                'status' => 200,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('myAnimal: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'tag_no' => $request->input('tag_no'),
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
            Log::error('myAnimal: General error', [
                'farmer_id' => $farmer->id ?? null,
                'tag_no' => $request->input('tag_no'),
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

