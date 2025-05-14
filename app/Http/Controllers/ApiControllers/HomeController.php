<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Group;
use App\Models\Slider;
use App\Models\State;
use App\Models\FarmerSlider;
use App\Models\City;
use App\Models\MyAnimal;
use App\Models\Subscription;
use App\Models\Cart;
use App\Models\CategoryImages;
use App\Models\SubcategoryImages;
use App\Models\Product;
use App\Models\FarmerNotification;
use App\Models\CheckMyFeedBuy;
use App\Models\SubscriptionBuy;
use App\Models\Canister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function createGroup(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('CreateGroup: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('CreateGroup: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Retrieve Authentication header
            $authentication = $request->header('Authentication');

            if (!$authentication) {
                Log::warning('CreateGroup: Missing Authentication header', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Authenticate farmer
            $farmer = Farmer::where('auth', $authentication)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('CreateGroup: Authentication failed', [
                    'ip' => $request->ip(),
                    'authentication' => $authentication,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Prepare group data
            $data = [
                'farmer_id' => $farmer->id,
                'name' => $request->input('name'),
                'ip' => $request->ip(),
                'is_active' => 1,
                'date' => Carbon::now('Asia/Kolkata'),
            ];

            // Insert group
            Group::create($data);

            Log::info('CreateGroup: Group created successfully', [
                'farmer_id' => $farmer->id,
                'group_name' => $data['name'],
                'ip' => $data['ip'],
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => [],
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('CreateGroup: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('CreateGroup: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

   public function getGroup(Request $request)
    {
        Log::info('getGroup request', [
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        // Validate inputs
        $token = $request->header('Authentication');
        $validator = Validator::make(['Authentication' => $token], [
            // 'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
        ]);

        if ($validator->fails()) {
            Log::warning('getGroup: Validation failed', [
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

            Log::debug('getGroup: Farmer query result', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'farmer_found' => $farmer ? true : false,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('getGroup: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Fetch groups
            $groups = Group::where('farmer_id', $farmer->id)
                ->where('is_active', 1)
                ->get();

            // Count total animals
            $totalAnimals = MyAnimal::where('farmer_id', $farmer->id)->count();

            $data = [];
            $serialNumber = 1;

            foreach ($groups as $group) {
                // Count animals in this group
                $animalCount = MyAnimal::where('farmer_id', $farmer->id)
                    ->where('assign_to_group', $group->id)
                    ->count();

                $data[] = [
                    's_no' => $serialNumber,
                    'value' => $group->id,
                    'label' => $group->name,
                    'animal_count' => $animalCount,
                ];
                $serialNumber++;
            }

            Log::info('getGroup: Groups retrieved successfully', [
                'farmer_id' => $farmer->id,
                'group_count' => count($data),
                'total_animals' => $totalAnimals,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
                'total' => $totalAnimals,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('getGroup: Database error', [
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
            Log::error('getGroup: General error', [
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

    public function getCattle(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('GetCattle: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'farmer_id' => 'required|integer|exists:tbl_farmers,id',
                'assign_to_group' => 'required|integer|exists:tbl_group,id',
                'milking' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('GetCattle: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('id', $request->input('farmer_id'))
                ->where('is_active', 1)
                ->first();

            Log::debug('GetCattle: Farmer query result', [
                'farmer_id' => $request->input('farmer_id'),
                'farmer_found' => $farmer ? $farmer->id : null,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('GetCattle: Authentication failed', [
                    'ip' => $request->ip(),
                    'farmer_id' => $request->input('farmer_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied! from farmer end',
                    'status' => 201,
                ], 403);
            }

            // Fetch distinct animal types
            $query = MyAnimal::select('animal_type')
                ->distinct()
                ->where('farmer_id', $farmer->id)
                ->where('assign_to_group', $request->input('assign_to_group'));

            if ($request->filled('milking')) {
                $query->where('animal_type', 'Milking');
            }

            $animalTypes = $query->get();

            $data = [];
            $serialNumber = 1;

            foreach ($animalTypes as $animal) {
                $data[] = [
                    'value' => $animal->animal_type,
                    'label' => $animal->animal_type,
                ];
                $serialNumber++;
            }

            Log::info('GetCattle: Animal types retrieved successfully', [
                'farmer_id' => $farmer->id,
                'assign_to_group' => $request->input('assign_to_group'),
                'milking' => $request->input('milking'),
                'animal_type_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('GetCattle: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('GetCattle: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function getTagNo(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('GetTagNo: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'farmer_id' => 'required|integer|exists:tbl_farmers,id',
                'assign_to_group' => 'required|integer|exists:tbl_group,id',
                'animal_type' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('GetTagNo: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('id', $request->input('farmer_id'))
                ->where('is_active', 1)
                ->first();

            Log::debug('GetTagNo: Farmer query result', [
                'farmer_id' => $request->input('farmer_id'),
                'farmer_found' => $farmer ? $farmer->id : null,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('GetTagNo: Authentication failed', [
                    'ip' => $request->ip(),
                    'farmer_id' => $request->input('farmer_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied! from farmer end',
                    'status' => 201,
                ], 403);
            }

            // Fetch tag numbers
            $query = MyAnimal::select('tag_no')
                ->where('farmer_id', $farmer->id)
                ->where('assign_to_group', $request->input('assign_to_group'))
                ->where('animal_type', $request->input('animal_type'));

            Log::debug('GetTagNo: Query SQL', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);

            $tagData = $query->get();

            $data = [];
            $serialNumber = 1;

            foreach ($tagData as $tag) {
                $data[] = [
                    'value' => $tag->tag_no,
                    'label' => $tag->tag_no,
                ];
                $serialNumber++;
            }

            Log::info('GetTagNo: Tag numbers retrieved successfully', [
                'farmer_id' => $farmer->id,
                'assign_to_group' => $request->input('assign_to_group'),
                'animal_type' => $request->input('animal_type'),
                'tag_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('GetTagNo: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('GetTagNo: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function getBullTagNo(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('GetBullTagNo: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'farmer_id' => 'required|integer|exists:tbl_farmers,id',
            ]);

            if ($validator->fails()) {
                Log::warning('GetBullTagNo: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('id', $request->input('farmer_id'))
                ->where('is_active', 1)
                ->first();

            Log::debug('GetBullTagNo: Farmer query result', [
                'farmer_id' => $request->input('farmer_id'),
                'farmer_found' => $farmer ? $farmer->id : null,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('GetBullTagNo: Authentication failed', [
                    'ip' => $request->ip(),
                    'farmer_id' => $request->input('farmer_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied! from farmer end',
                    'status' => 201,
                ], 403);
            }

            // Fetch tag numbers for bulls
            $query = MyAnimal::select('tag_no')
                ->where('farmer_id', $farmer->id)
                ->where('animal_type', 'Bull');

            Log::debug('GetBullTagNo: Query SQL', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);

            $tagData = $query->get();

            $data = [];
            $serialNumber = 1;

            foreach ($tagData as $tag) {
                $data[] = [
                    'value' => $tag->tag_no,
                    'label' => $tag->tag_no,
                ];
                $serialNumber++;
            }

            Log::info('GetBullTagNo: Tag numbers retrieved successfully', [
                'farmer_id' => $farmer->id,
                'animal_type' => 'Bull',
                'tag_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('GetBullTagNo: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('GetBullTagNo: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function getSemenBulls(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('GetSemenBulls: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'farmer_id' => 'required|integer|exists:tbl_farmers,id',
            ]);

            if ($validator->fails()) {
                Log::warning('GetSemenBulls: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('id', $request->input('farmer_id'))
                ->where('is_active', 1)
                ->first();

            Log::debug('GetSemenBulls: Farmer query result', [
                'farmer_id' => $request->input('farmer_id'),
                'farmer_found' => $farmer ? $farmer->id : null,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('GetSemenBulls: Authentication failed', [
                    'ip' => $request->ip(),
                    'farmer_id' => $request->input('farmer_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Fetch semen bulls from canister
            $query = Canister::select('id', 'bull_name')
                ->where('farmer_id', $farmer->id)
                ->where('farm_bull', 'No');

            Log::debug('GetSemenBulls: Query SQL', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);

            $tagData = $query->get();

            $data = [];
            $serialNumber = 1;

            foreach ($tagData as $tag) {
                $data[] = [
                    'value' => $tag->id,
                    'label' => $tag->bull_name,
                ];
                $serialNumber++;
            }

            Log::info('GetSemenBulls: Semen bulls retrieved successfully', [
                'farmer_id' => $farmer->id,
                'bull_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('GetSemenBulls: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('GetSemenBulls: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function getAnimalData(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('GetAnimalData: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'farmer_id' => 'required|integer|exists:tbl_farmers,id',
                'tag_no' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning('GetAnimalData: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('id', $request->input('farmer_id'))
                ->where('is_active', 1)
                ->first();

            Log::debug('GetAnimalData: Farmer query result', [
                'farmer_id' => $request->input('farmer_id'),
                'farmer_found' => $farmer ? $farmer->id : null,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('GetAnimalData: Authentication failed', [
                    'ip' => $request->ip(),
                    'farmer_id' => $request->input('farmer_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Fetch animal data
            $query = MyAnimal::select('id', 'tag_no', 'animal_name', 'animal_type', 'breed_type', 'dob', 'animal_gender')
                ->where('farmer_id', $farmer->id)
                ->where('tag_no', $request->input('tag_no'));

            Log::debug('GetAnimalData: Query SQL', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);

            $animal = $query->first();

            if (!$animal) {
                Log::warning('GetAnimalData: No animal found', [
                    'farmer_id' => $farmer->id,
                    'tag_no' => $request->input('tag_no'),
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'No animal found for the given tag number',
                    'status' => 201,
                ], 404);
            }

            $data = [
                'id' => $animal->id,
                'tag_no' => $animal->tag_no,
                'animal_name' => $animal->animal_name,
                'animal_type' => $animal->animal_type,
                'breed_type' => $animal->breed_type,
                'dob' => $animal->dob,
                'animal_gender' => $animal->animal_gender,
            ];

            Log::info('GetAnimalData: Animal data retrieved successfully', [
                'farmer_id' => $farmer->id,
                'tag_no' => $animal->tag_no,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('GetAnimalData: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'tag_no' => $request->input('tag_no') ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('GetAnimalData: General error', [
                'farmer_id' => $farmer->id ?? null,
                'tag_no' => $request->input('tag_no') ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function subscriptionPlan(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('SubscriptionPlan: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'farmer_id' => 'required|integer|exists:tbl_farmers,id',
            ]);

            if ($validator->fails()) {
                Log::warning('SubscriptionPlan: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('id', $request->input('farmer_id'))
                ->where('is_active', 1)
                ->first();

            Log::debug('SubscriptionPlan: Farmer query result', [
                'farmer_id' => $request->input('farmer_id'),
                'farmer_found' => $farmer ? $farmer->id : null,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('SubscriptionPlan: Authentication failed', [
                    'ip' => $request->ip(),
                    'farmer_id' => $request->input('farmer_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied! from farmer end',
                    'status' => 201,
                ], 403);
            }

            // Fetch active subscription plans
            $subscriptions = Subscription::where('is_active', 1)->get();

            // Fetch farmer's active subscription
            $currentDate = Carbon::today('Asia/Kolkata')->toDateString();
            $subscribed = SubscriptionBuy::where('farmer_id', $farmer->id)
                ->where('expiry_date', '>=', $currentDate)
                ->orderBy('id', 'desc')
                ->first();

            $data = [];

            foreach ($subscriptions as $subscription) {
                $active = 0;
                $planDetails = [];

                if ($subscribed && $subscribed->plan_id == $subscription->id) {
                    $active = 1;
                    $expiryDate = Carbon::parse($subscribed->expiry_date, 'Asia/Kolkata')->format('d-m-y');
                    $planDetails = [
                        'months' => $subscribed->months,
                        'price' => $subscribed->price,
                        'expiry_date' => $expiryDate,
                    ];
                } elseif ($subscribed) {
                    $active = 2;
                }

                $data[] = [
                    'id' => $subscription->id,
                    'service_name' => $subscription->service_name,
                    'monthly_price' => $subscription->monthly_price,
                    'monthly_description' => $subscription->monthly_description,
                    'monthly_service' => $subscription->monthly_service,
                    'quarterly_price' => $subscription->quarterly_price,
                    'quarterly_description' => $subscription->quarterly_description,
                    'quarterly_service' => $subscription->quarterly_service,
                    'halfyearly_price' => $subscription->halfyearly_price,
                    'halfyearly_description' => $subscription->halfyearly_description,
                    'halfyearly_service' => $subscription->halfyearly_service,
                    'yearly_price' => $subscription->yearly_price,
                    'yearly_description' => $subscription->yearly_description,
                    'yearly_service' => $subscription->yearly_service,
                    'animals' => $subscription->animals,
                    'doctor_calls' => $subscription->doctor_calls,
                    'active' => $active,
                    'plan_details' => $planDetails,
                ];
            }

            Log::info('SubscriptionPlan: Subscription plans retrieved successfully', [
                'farmer_id' => $farmer->id,
                'subscription_count' => count($data),
                'has_active_subscription' => $subscribed ? $subscribed->plan_id : null,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('SubscriptionPlan: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('SubscriptionPlan: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    // public function homeData(Request $request)
    // {
    //     try {
    //         // Check if POST data exists
    //         if (!$request->isMethod('post') || empty($request->all())) {
    //             Log::warning('HomeData: Missing POST data', [
    //                 'ip' => $request->ip(),
    //                 'url' => $request->fullUrl(),
    //             ]);
    //             return response()->json([
    //                 'message' => 'Please Insert Data',
    //                 'status' => 201,
    //             ], 422);
    //         }

    //         // Validate inputs
    //         $validator = Validator::make($request->all(), [
    //             'farmer_id' => 'required|integer|exists:tbl_farmers,id',
    //             'fcm_token' => 'nullable|string|max:255',
    //             'lang' => 'nullable|string|in:en,hi,mr,pu',
    //         ]);

    //         if ($validator->fails()) {
    //             Log::warning('HomeData: Validation failed', [
    //                 'ip' => $request->ip(),
    //                 'errors' => $validator->errors(),
    //                 'url' => $request->fullUrl(),
    //             ]);
    //             return response()->json([
    //                 'message' => $validator->errors()->first(),
    //                 'status' => 201,
    //             ], 422);
    //         }

    //         // Set defaults
    //         $fcmToken = $request->input('fcm_token', '');
    //         $lang = $request->input('lang', 'en');

    //         // Authenticate farmer
    //         $farmer = Farmer::where('id', $request->input('farmer_id'))
    //             ->where('is_active', 1)
    //             ->first();

    //         Log::debug('HomeData: Farmer query result', [
    //             'farmer_id' => $request->input('farmer_id'),
    //             'farmer_found' => $farmer ? $farmer->id : null,
    //             'ip' => $request->ip(),
    //         ]);

    //         if (!$farmer) {
    //             Log::warning('HomeData: Authentication failed', [
    //                 'ip' => $request->ip(),
    //                 'farmer_id' => $request->input('farmer_id'),
    //             ]);
    //             return response()->json([
    //                 'message' => 'Permission Denied! from farmer end',
    //                 'status' => 201,
    //             ], 403);
    //         }

    //         // Update fcm_token if different
    //         if ($fcmToken && $fcmToken !== $farmer->fcm_token) {
    //             $farmer->fcm_token = $fcmToken;
    //             $farmer->save();
    //             Log::info('HomeData: FCM token updated', [
    //                 'farmer_id' => $farmer->id,
    //                 'fcm_token' => $fcmToken,
    //                 'ip' => $request->ip(),
    //             ]);
    //         }

    //         // Fetch slider data
    //         $sliders = Slider::where('is_active', 1)->get();
    //         $sliderData = $sliders->map(function ($slide) {
    //             return $slide->image ? asset($slide->image) : '';
    //         })->filter()->toArray();

    //         // Fetch farmer slider data
    //         $farmerSliders = FarmerSlider::where('is_active', 1)->get();
    //         $farmerSliderData = $farmerSliders->map(function ($farmerSlide) {
    //             return [
    //                 'image' => $farmerSlide->image ? asset($farmerSlide->image) : '',
    //             ];
    //         })->toArray();

    //         // Fetch category and subcategory data
    //         $categories = CategoryImages::where('is_active', 1)->get();
    //         $categoryData = [];

    //         foreach ($categories as $category) {
    //             $subcategories = SubcategoryImages::where('is_active', 1)
    //                 ->where('category_id', $category->id)
    //                 ->orderBy('seq', 'asc')
    //                 ->get();

    //             $subCategoryData = $subcategories->map(function ($subcategory) use ($lang) {
    //                 if ($lang === 'hi') {
    //                     $subImage = $subcategory->image_hindi;
    //                 } elseif ($lang === 'mr') {
    //                     $subImage = $subcategory->image_marathi;
    //                 } elseif ($lang === 'pu') {
    //                     $subImage = $subcategory->image_punjabi;
    //                 } else {
    //                     $subImage = $subcategory->image;
    //                 }
                    
    //                 return [
    //                     'id' => $subcategory->id,
    //                     'name' => $subcategory->name,
    //                     'image' => $subImage ? asset($subImage) : '',
    //                 ];
    //             })->toArray();

    //             // $catImage = match ($lang) {
    //             //     'hi' => $category->image_hindi,
    //             //     'mr' => $category->image_marathi,
    //             //     'pu' => $category->image_punjabi,
    //             //     default => $category->image,
    //             // };
    //             if ($lang === 'hi') {
    //                 $catImage = $category->image_hindi;
    //             } elseif ($lang === 'mr') {
    //                 $catImage = $category->image_marathi;
    //             } elseif ($lang === 'pu') {
    //                 $catImage = $category->image_punjabi;
    //             } else {
    //                 $catImage = $category->image;
    //             }
                
    //             $categoryData[] = [
    //                 'id' => $category->id,
    //                 'name' => $category->name,
    //                 'image' => $catImage ? asset($catImage) : '',
    //                 'subcatgory' => $subCategoryData,
    //             ];
    //         }

    //         // Fetch trending products
    //         $products = Product::where('tranding_products', 1)
    //             ->where('is_active', 1)
    //             ->where('is_admin', 1)
    //             ->get();

    //         $productData = $products->map(function ($product) use ($lang, $farmer) {
    //             $images = [];
    //             if ($product->image) {
    //                 $imageArray = json_decode($product->image, true);
    //                 if (is_array($imageArray) && !empty($imageArray)) {
    //                     $images = array_map(function ($img) {
    //                         return asset($img);
    //                     }, $imageArray);
                        
    //                 } else {
    //                     $images = [asset($product->image)];
    //                 }
    //             }

    //             $video = $product->video ? asset($product->video) : '';
    //             $stock = $product->inventory != 0 ? 'In Stock' : 'Out of Stock';
    //             $discount = (int)$product->mrp - (int)$product->selling_price;
    //             $percent = $discount > 0 ? round($discount / $product->mrp * 100) : 0;

    //             switch ($lang) {
    //                 case 'hi':
    //                     $productDetails = [
    //                         'name' => $product->name_hindi,
    //                         'description' => $product->description_hindi,
    //                     ];
    //                     break;
    //                 case 'mr':
    //                     $productDetails = [
    //                         'name' => $product->name_marathi,
    //                         'description' => $product->description_marathi,
    //                     ];
    //                     break;
    //                 case 'pu':
    //                     $productDetails = [
    //                         'name' => $product->name_punjabi,
    //                         'description' => $product->description_punjabi,
    //                     ];
    //                     break;
    //                 default:
    //                     $productDetails = [
    //                         'name' => $product->name_english,
    //                         'description' => $product->description_english,
    //                     ];
    //             }
                

    //             return [
    //                 'pro_id' => $product->id,
    //                 'name' => $productDetails['name'],
    //                 'description' => $productDetails['description'],
    //                 'image' => $images,
    //                 'video' => $video,
    //                 'mrp' => $product->mrp,
    //                 'min_qty' => $product->min_qty ?? 1,
    //                 'selling_price' => $product->selling_price,
    //                 'suffix' => $product->suffix,
    //                 'stock' => $stock,
    //                 'percent' => $percent,
    //                 'vendor_id' => $product->added_by,
    //                 'is_admin' => $product->is_admin,
    //                 'offer' => $product->offer,
    //                 'product_cod' => $product->cod,
    //                 'is_cod' => $farmer->cod,
    //             ];
    //         })->toArray();

    //         // Fetch cart count
    //         $cartCount = Cart::where('farmer_id', $farmer->id)->count();

    //         // Fetch farmer notifications
    //         $notifications = FarmerNotification::where('farmer_id', $farmer->id)->get();
    //         $notificationData = $notifications->map(function ($notification) {
    //             return [
    //                 'id' => $notification->id,
    //                 'name' => $notification->name,
    //                 'image' => $notification->image ? asset($notification->image) : '',
    //                 'description' => $notification->dsc,
    //                 'date' => Carbon::parse($notification->date, 'Asia/Kolkata')->format('d-m-y, g:i a'),
    //             ];
    //         })->toArray();

    //         $notificationCount = $notifications->count();

    //         // Check feed purchase status
    //         $feedCheck = CheckMyFeedBuy::where('farmer_id', $farmer->id)
    //             ->where('payment_status', 1)
    //             ->first();
    //         $feedBuy = $feedCheck ? 1 : 0;

    //         // Define feed amount (hardcoded for testing)
    //         $feedAmount = config('app.feed_amount', 100.00);

    //         $data = [
    //             'slider' => $sliderData,
    //             'Farmer_slider' => $farmerSliderData,
    //             'Category_Data' => $categoryData,
    //             'product_data' => $productData,
    //             'notification_data' => $notificationData,
    //             'notification_count' => $notificationCount,
    //             'CartCount' => $cartCount,
    //             'feedBuy' => $feedBuy,
    //             'feedAmount' => $feedAmount,
    //         ];

    //         Log::info('HomeData: Home data retrieved successfully', [
    //             'farmer_id' => $farmer->id,
    //             'slider_count' => count($sliderData),
    //             'farmer_slider_count' => count($farmerSliderData),
    //             'category_count' => count($categoryData),
    //             'product_count' => count($productData),
    //             'notification_count' => $notificationCount,
    //             'cart_count' => $cartCount,
    //             'feed_buy' => $feedBuy,
    //             'ip' => $request->ip(),
    //         ]);

    //         return response()->json([
    //             'message' => 'Success!',
    //             'status' => 200,
    //             'data' => $data,
    //         ], 200);
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         Log::error('HomeData: Database error', [
    //             'farmer_id' => $farmer->id ?? null,
    //             'error' => $e->getMessage(),
    //             'sql' => $e->getSql(),
    //             'bindings' => $e->getBindings(),
    //         ]);
    //         return response()->json([
    //             'message' => 'Database error: ' . $e->getMessage(),
    //             'status' => 201,
    //         ], 500);
    //     } catch (\Exception $e) {
    //         Log::error('HomeData: General error', [
    //             'farmer_id' => $farmer->id ?? null,
    //             'error' => $e->getMessage(),
    //         ]);
    //         return response()->json([
    //             'message' => 'Error processing request: ' . $e->getMessage(),
    //             'status' => 201,
    //         ], 500);
    //     }
    // }

     public function homeData(Request $request)
    {
        try {
            // Get headers
            // $fcmToken = $request->header('Fcm-Token', '');
            $lang = $request->header('Lang', 'en');
            $authToken = $request->header('Authentication');

            // Validate inputs
            $validator = Validator::make([
                'Lang' => $lang,
                'Authentication' => $authToken,
            ], [
                'Lang' => 'nullable|string|in:en,hi,mr,pu',
                'Authentication' => 'required|string',
            ]);

            if ($validator->fails()) {
                Log::warning('HomeData: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('auth', $authToken)
                ->where('is_active', 1)
                ->first();

            Log::debug('HomeData: Farmer query result', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'farmer_found' => $farmer ? $farmer->id : null,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('HomeData: Authentication failed', [
                    'ip' => $request->ip(),
                    'auth_token' => $authToken,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 401);
            }

            // Update fcm_token if different
            // if ($fcmToken && $fcmToken !== $farmer->fcm_token) {
            //     $farmer->fcm_token = $fcmToken;
            //     $farmer->save();
            //     Log::info('HomeData: FCM token updated', [
            //         'farmer_id' => $farmer->id,
            //         'fcm_token' => $fcmToken,
            //         'ip' => $request->ip(),
            //     ]);
            // }

            // Fetch slider data
            $sliders = Slider::where('is_active', 1)->get();
            $sliderData = $sliders->map(function ($slide) {
                return $slide->image ? asset($slide->image) : '';
            })->filter()->toArray();

            // Fetch farmer slider data
            $farmerSliders = FarmerSlider::where('is_active', 1)->get();
            $farmerSliderData = $farmerSliders->map(function ($farmerSlide) {
                return [
                    'image' => $farmerSlide->image ? asset($farmerSlide->image) : '',
                ];
            })->toArray();

            // Fetch category and subcategory data
            $categories = CategoryImages::where('is_active', 1)->get();
            $categoryData = [];

            foreach ($categories as $category) {
                $subcategories = SubcategoryImages::where('is_active', 1)
                    ->where('category_id', $category->id)
                    ->orderBy('seq', 'asc')
                    ->get();

                $subCategoryData = $subcategories->map(function ($subcategory) use ($lang) {
                    $subImage = match ($lang) {
                        'hi' => $subcategory->image_hindi,
                        'mr' => $subcategory->image_marathi,
                        'pu' => $subcategory->image_punjabi,
                        default => $subcategory->image,
                    };

                    return [
                        'id' => $subcategory->id,
                        'name' => $subcategory->name,
                        'image' => $subImage ? asset($subImage) : '',
                    ];
                })->toArray();

                $catImage = match ($lang) {
                    'hi' => $category->image_hindi,
                    'mr' => $category->image_marathi,
                    'pu' => $category->image_punjabi,
                    default => $category->image,
                };

                $categoryData[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image' => $catImage ? asset($catImage) : '',
                    'subcatgory' => $subCategoryData,
                ];
            }

            // Fetch trending products
            $products = Product::where('tranding_products', 1)
                ->where('is_active', 1)
                ->where('is_admin', 1)
                ->get();

            $productData = $products->map(function ($product) use ($lang, $farmer) {
                $images = [];
                if ($product->image) {
                    $imageArray = json_decode($product->image, true);
                    if (is_array($imageArray) && !empty($imageArray)) {
                        $images = array_map(function ($img) {
                            return asset($img);
                        }, $imageArray);
                    } else {
                        $images = [asset($product->image)];
                    }
                }

                $video = $product->video ? asset($product->video) : '';
                $stock = $product->inventory != 0 ? 'In Stock' : 'Out of Stock';
                $discount = (int)$product->mrp - (int)$product->selling_price;
                $percent = $discount > 0 ? round($discount / $product->mrp * 100) : 0;

                $productDetails = match ($lang) {
                    'hi' => [
                        'name' => $product->name_hindi,
                        'description' => $product->description_hindi,
                    ],
                    'mr' => [
                        'name' => $product->name_marathi,
                        'description' => $product->description_marathi,
                    ],
                    'pu' => [
                        'name' => $product->name_punjabi,
                        'description' => $product->description_punjabi,
                    ],
                    default => [
                        'name' => $product->name_english,
                        'description' => $product->description_english,
                    ],
                };
                return [
                    'pro_id' => $product->id,
                    'name' => $productDetails['name'],
                    'description' => $productDetails['description'],
                    'image' => $images,
                    'video' => $video,
                    'mrp' => $product->mrp,
                    'min_qty' => $product->min_qty ?? 1,
                    'selling_price' => $product->selling_price,
                    'suffix' => $product->suffix,
                    'stock' => $stock,
                    'percent' => $percent,
                    'vendor_id' => $product->added_by,
                    'is_admin' => (int) $product->is_admin,
                    'offer' => $product->offer,
                    'product_cod' => $product->cod,
                    'is_cod' => $farmer->cod,
                ];
            })->toArray();

            // Fetch cart count
            $cartCount = Cart::where('farmer_id', $farmer->id)->count();

            // Fetch farmer notifications
            $notifications = FarmerNotification::where('farmer_id', $farmer->id)->get();
            $notificationData = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'name' => $notification->name,
                    'image' => $notification->image ? asset($notification->image) : '',
                    'description' => $notification->dsc,
                    'date' => Carbon::parse($notification->date, 'Asia/Kolkata')->format('d-m-y, g:i a'),
                ];
            })->toArray();

            $notificationCount = $notifications->count();

            // Check feed purchase status
            $feedCheck = CheckMyFeedBuy::where('farmer_id', $farmer->id)
                ->where('payment_status', 1)
                ->first();
            $feedBuy = $feedCheck ? 1 : 0;

            // Define feed amount
            $feedAmount = config('app.feed_amount', 100.00);

            $data = [
                'slider' => $sliderData,
                'Farmer_slider' => $farmerSliderData,
                'Category_Data' => $categoryData,
                'product_data' => $productData,
                'notification_data' => $notificationData,
                'notification_count' => $notificationCount,
                'CartCount' => $cartCount,
                'feedBuy' => $feedBuy,
                'feedAmount' => $feedAmount,
            ];

            Log::info('HomeData: Home data retrieved successfully', [
                'farmer_id' => $farmer->id,
                'slider_count' => count($sliderData),
                'farmer_slider_count' => count($farmerSliderData),
                'category_count' => count($categoryData),
                'product_count' => count($productData),
                'notification_count' => $notificationCount,
                'cart_count' => $cartCount,
                'feed_buy' => $feedBuy,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('HomeData: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('HomeData: General error', [
                'farmer_id' => $farmer->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
    public function getState(Request $request)
    {
        try {
            // Fetch all states
            $states = State::all();

            $data = $states->map(function ($state) {
                // Remove bracketed content from state name (e.g., [IN])
                $label = preg_replace('/\s*\[.*?\]/', '', $state->state_name);
                return [
                    'value' => $state->id,
                    'label' => $label,
                ];
            })->toArray();

            Log::info('GetState: States retrieved successfully', [
                'state_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('GetState: Database error', [
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
            Log::error('GetState: General error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

     public function getCity(Request $request, $state_id)
    {
        Log::info('getCity request', [
            'state_id' => $state_id,
            // 'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        // Validate inputs
        $token = $request->header('Authentication');
        $validator = Validator::make([
            'state_id' => $state_id,
            // 'Authentication' => $token,
        ], [
            'state_id' => 'required|integer|exists:all_states,id',
            // 'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
            'state_id.exists' => 'Invalid state ID',
        ]);

        if ($validator->fails()) {
            Log::warning('getCity: Validation failed', [
                'state_id' => $state_id,
                'errors' => $validator->errors(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 400,
                'data' => [],
            ], 400);
        }

        try {
            // Authenticate user by token
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('getCity: Invalid or inactive user for token', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Fetch cities for the given state_id
            $cities = City::where('state_id', $state_id)->get();

            $data = $cities->map(function ($city) {
                return [
                    'value' => $city->id,
                    'label' => $city->city_name,
                ];
            })->toArray();

            Log::info('getCity: Cities retrieved successfully', [
                'farmer_id' => $farmer->id,
                'state_id' => $state_id,
                'city_count' => count($data),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('getCity: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'state_id' => $state_id,
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
            Log::error('getCity: General error', [
                'farmer_id' => $farmer->id ?? null,
                'state_id' => $state_id,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function buyPlan(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('BuyPlan: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'farmer_id' => 'required|integer|exists:tbl_farmers,id',
                'plan_id' => 'required|integer|exists:tbl_subscription,id',
                'type' => 'required|string|in:monthly_price,quarterly_price,halfyearly_price,yearly_price',
                'months' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('BuyPlan: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('id', $request->input('farmer_id'))
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('BuyPlan: Authentication failed', [
                    'ip' => $request->ip(),
                    'farmer_id' => $request->input('farmer_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Fetch plan data
            $plan = Subscription::where('id', $request->input('plan_id'))
                ->where('is_active', 1)
                ->first();

            if (!$plan) {
                Log::warning('BuyPlan: Invalid plan', [
                    'farmer_id' => $farmer->id,
                    'plan_id' => $request->input('plan_id'),
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 422);
            }

            // Prepare subscription purchase data
            $startDate = Carbon::today('Asia/Kolkata')->toDateString();
            $expiryDate = Carbon::today('Asia/Kolkata')->addMonths($request->input('months'))->toDateString();
            $txnId = mt_rand(999999, 999999999999);
            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            $subscriptionData = [
                'farmer_id' => $farmer->id,
                'plan_id' => $request->input('plan_id'),
                'months' => $request->input('months'),
                'price' => $plan->{$request->input('type')},
                'animals' => $plan->animals,
                'doctor_calls' => $plan->doctor_calls,
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'payment_status' => 0,
                'txn_id' => $txnId,
                'date' => $currentDateTime,
                'gateway' => 'CC Avenue',
            ];

            // Insert subscription purchase
            $subscriptionBuy = SubscriptionBuy::create($subscriptionData);
            $reqId = $subscriptionBuy->id;

            // Prepare CCAvenue payment data
            $successUrl = url('/api/plan-payment-success');
            $failUrl = url('/api/payment-failed');

            $postData = [
                'txn_id' => '',
                'merchant_id' => config('app.ccavenue_merchant_id'),
                'order_id' => $txnId,
                'amount' => $plan->{$request->input('type')},
                'currency' => 'INR',
                'redirect_url' => $successUrl,
                'cancel_url' => $failUrl,
                'billing_name' => $farmer->name ?? 'Unknown',
                'billing_address' => $farmer->village ?? 'Unknown',
                'billing_city' => $farmer->city ?? 'Unknown',
                'billing_state' => $farmer->state ?? 'Unknown',
                'billing_zip' => $farmer->pincode ?? '000000',
                'billing_country' => 'India',
                'billing_tel' => $farmer->phone ?? '0000000000',
                'billing_email' => '',
                'merchant_param1' => 'Plan Payment',
            ];

            // Generate encrypted data for CCAvenue
            $merchantData = '';
            foreach ($postData as $key => $value) {
                $merchantData .= $key . '=' . $value . '&';
            }

            $workingKey = config('app.ccavenue_working_key');
            $accessCode = config('app.ccavenue_access_code');

            $length = strlen(md5($workingKey));
            $binString = '';
            $count = 0;
            while ($count < $length) {
                $subString = substr(md5($workingKey), $count, 2);
                $packedString = pack('H*', $subString);
                $binString .= $packedString;
                $count += 2;
            }
            $key = $binString;

            $initVector = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
            $encryptedData = openssl_encrypt($merchantData, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
            $encryptedDataHex = bin2hex($encryptedData);

            $responseData = [
                'order_id' => $reqId,
                'access_code' => $accessCode,
                'redirect_url' => $successUrl,
                'cancel_url' => $failUrl,
                'enc_val' => $encryptedDataHex,
                'plain' => $merchantData,
                'merchant_param1' => 'Plan Payment',
            ];

            Log::info('BuyPlan: Subscription purchase initiated successfully', [
                'farmer_id' => $farmer->id,
                'plan_id' => $request->input('plan_id'),
                'type' => $request->input('type'),
                'months' => $request->input('months'),
                'txn_id' => $txnId,
                'req_id' => $reqId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $responseData,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('BuyPlan: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'plan_id' => $request->input('plan_id') ?? null,
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
            Log::error('BuyPlan: General error', [
                'farmer_id' => $farmer->id ?? null,
                'plan_id' => $request->input('plan_id') ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function phonePeBuyPlan(Request $request)
    {
        try {
            // Check if POST data exists
            if (!$request->isMethod('post') || empty($request->all())) {
                Log::warning('PhonePeBuyPlan: Missing POST data', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'farmer_id' => 'required|integer|exists:tbl_farmers,id',
                'plan_id' => 'required|integer|exists:tbl_subscription,id',
                'type' => 'required|string|in:monthly_price,quarterly_price,halfyearly_price,yearly_price',
                'months' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('PhonePeBuyPlan: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Authenticate farmer
            $farmer = Farmer::where('id', $request->input('farmer_id'))
                ->where('is_active', 1)
                ->first();

            if (!$farmer) {
                Log::warning('PhonePeBuyPlan: Authentication failed', [
                    'ip' => $request->ip(),
                    'farmer_id' => $request->input('farmer_id'),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Fetch plan data
            $plan = Subscription::where('id', $request->input('plan_id'))
                ->where('is_active', 1)
                ->first();

            if (!$plan) {
                Log::warning('PhonePeBuyPlan: Invalid plan', [
                    'farmer_id' => $farmer->id,
                    'plan_id' => $request->input('plan_id'),
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 422);
            }

            // Prepare subscription purchase data
            $startDate = Carbon::today('Asia/Kolkata')->toDateString();
            $expiryDate = Carbon::today('Asia/Kolkata')->addMonths($request->input('months'))->toDateString();
            $txnId = bin2hex(random_bytes(12));
            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();

            $subscriptionData = [
                'farmer_id' => $farmer->id,
                'plan_id' => $request->input('plan_id'),
                'months' => $request->input('months'),
                'price' => $plan->{$request->input('type')},
                'animals' => $plan->animals,
                'doctor_calls' => $plan->doctor_calls,
                'start_date' => $startDate,
                'expiry_date' => $expiryDate,
                'payment_status' => 0,
                'txn_id' => $txnId,
                'date' => $currentDateTime,
                'gateway' => 'Phone Pe',
            ];

            // Insert subscription purchase
            $subscriptionBuy = SubscriptionBuy::create($subscriptionData);
            $reqId = $subscriptionBuy->id;

            // Initiate PhonePe payment
            $successUrl = url('/api/phone-pe-plan-payment-success');
            $param1 = 'Plan Payment';
            $response = $this->initiatePhonePePayment(
                $txnId,
                $plan->{$request->input('type')},
                $farmer->phone ?? '0000000000',
                $successUrl,
                $param1
            );

            if ($response && isset($response->code) && $response->code === 'PAYMENT_INITIATED') {
                $responseData = [
                    'url' => $response->data['instrumentResponse']['redirectInfo']['url'],
                    'redirect_url' => $successUrl,
                    'merchant_param1' => $param1,
                    'order_id' => $reqId,
                ];

                Log::info('PhonePeBuyPlan: Subscription purchase initiated successfully', [
                    'farmer_id' => $farmer->id,
                    'plan_id' => $request->input('plan_id'),
                    'type' => $request->input('type'),
                    'months' => $request->input('months'),
                    'txn_id' => $txnId,
                    'req_id' => $reqId,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $responseData,
                ], 200);
            }

            Log::warning('PhonePeBuyPlan: PhonePe payment initiation failed', [
                'farmer_id' => $farmer->id,
                'plan_id' => $request->input('plan_id'),
                'txn_id' => $txnId,
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Some error occurred!',
                'status' => 201,
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('PhonePeBuyPlan: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'plan_id' => $request->input('plan_id') ?? null,
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
            Log::error('PhonePeBuyPlan: General error', [
                'farmer_id' => $farmer->id ?? null,
                'plan_id' => $request->input('plan_id') ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }   

    public function planPaymentSuccess(Request $request)
    {
        try {
            $encResponse = $request->input('encResp');
            if (!$encResponse) {
                Log::warning('PlanPaymentSuccess: Missing encResp', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response('Aborted', 400)->header('Content-Type', 'text/plain');
            }

            $workingKey = config('app.ccavenue_working_key');
            $rcvdString = $this->decryptCCAvenue($encResponse, $workingKey);
            if (!$rcvdString) {
                Log::error('PlanPaymentSuccess: Decryption failed', [
                    'encResp' => $encResponse,
                    'ip' => $request->ip(),
                ]);
                return response('Aborted', 400)->header('Content-Type', 'text/plain');
            }

            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();
            $decryptValues = explode('&', $rcvdString);
            $orderStatus = '';
            $txnId = '';

            foreach ($decryptValues as $info) {
                $pair = explode('=', $info, 2);
                if (count($pair) === 2) {
                    if ($pair[0] === 'order_status') {
                        $orderStatus = $pair[1];
                    } elseif ($pair[0] === 'order_id') {
                        $txnId = $pair[1];
                    }
                }
            }

            $responseData = [
                'body' => json_encode($decryptValues),
                'date' => $currentDateTime,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ];

            $lastId = DB::table('tbl_ccavenue_response')->insertGetId($responseData);

            Log::info('PlanPaymentSuccess: CCAvenue response logged', [
                'last_id' => $lastId,
                'txn_id' => $txnId,
                'order_status' => $orderStatus,
                'ip' => $request->ip(),
            ]);

            if ($orderStatus === 'Success') {
                $order = SubscriptionBuy::where('txn_id', $txnId)
                    ->where('payment_status', 0)
                    ->first();

                if ($order) {
                    $order->update([
                        'payment_status' => 1,
                        'cc_response' => json_encode($decryptValues),
                    ]);

                    Log::info('PlanPaymentSuccess: Subscription updated', [
                        'order_id' => $order->id,
                        'txn_id' => $txnId,
                        'ip' => $request->ip(),
                    ]);

                    return response('Success', 200)->header('Content-Type', 'text/plain');
                }

                Log::warning('PlanPaymentSuccess: Order not found', [
                    'txn_id' => $txnId,
                    'ip' => $request->ip(),
                ]);
                return response('Aborted', 400)->header('Content-Type', 'text/plain');
            }

            $statusText = $orderStatus === 'Failure' ? 'Failure' : 'Aborted';
            Log::warning('PlanPaymentSuccess: Payment not successful', [
                'txn_id' => $txnId,
                'order_status' => $orderStatus,
                'ip' => $request->ip(),
            ]);

            return response($statusText, 400)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            Log::error('PlanPaymentSuccess: General error', [
                'txn_id' => $txnId ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response('Aborted', 500)->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Handle PhonePe payment success callback.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function phonePePlanPaymentSuccess(Request $request)
    {
        try {
            $responseBase64 = $request->input('response');
            $xVerify = $request->header('X-VERIFY');

            if (!$responseBase64 || !$xVerify) {
                Log::warning('PhonePePlanPaymentSuccess: Missing response or X-VERIFY', [
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);
                return response('Aborted', 400)->header('Content-Type', 'text/plain');
            }

            $saltKey = config('app.phonepe.salt_key');
            $saltIndex = config('app.phonepe.salt_index');
            $stringToHash = $responseBase64 . '/pg/v1/status' . $saltKey;
            $expectedXVerify = hash('sha256', $stringToHash) . '###' . $saltIndex;

            if ($xVerify !== $expectedXVerify) {
                Log::error('PhonePePlanPaymentSuccess: Invalid X-VERIFY', [
                    'received_x_verify' => $xVerify,
                    'expected_x_verify' => $expectedXVerify,
                    'ip' => $request->ip(),
                ]);
                return response('Aborted', 400)->header('Content-Type', 'text/plain');
            }

            $responseJson = base64_decode($responseBase64);
            $responseData = json_decode($responseJson, true);

            if (!$responseData || !isset($responseData['code']) || !isset($responseData['data']['merchantTransactionId'])) {
                Log::error('PhonePePlanPaymentSuccess: Invalid response data', [
                    'response' => $responseJson,
                    'ip' => $request->ip(),
                ]);
                return response('Aborted', 400)->header('Content-Type', 'text/plain');
            }

            $txnId = $responseData['data']['merchantTransactionId'];
            $orderStatus = $responseData['code'];

            $currentDateTime = Carbon::now('Asia/Kolkata')->toDateTimeString();
            $logData = [
                'body' => json_encode($responseData),
                'date' => $currentDateTime,
                'created_at' => $currentDateTime,
                'updated_at' => $currentDateTime,
            ];

            $lastId = DB::table('tbl_phonepe_response')->insertGetId($logData);

            Log::info('PhonePePlanPaymentSuccess: PhonePe response logged', [
                'last_id' => $lastId,
                'txn_id' => $txnId,
                'order_status' => $orderStatus,
                'ip' => $request->ip(),
            ]);

            if ($orderStatus === 'PAYMENT_SUCCESS') {
                $order = SubscriptionBuy::where('txn_id', $txnId)
                    ->where('payment_status', 0)
                    ->first();

                if ($order) {
                    $order->update([
                        'payment_status' => 1,
                        'phonepe_response' => json_encode($responseData),
                    ]);

                    Log::info('PhonePePlanPaymentSuccess: Subscription updated', [
                        'order_id' => $order->id,
                        'txn_id' => $txnId,
                        'ip' => $request->ip(),
                    ]);

                    return response('Success', 200)->header('Content-Type', 'text/plain');
                }

                Log::warning('PhonePePlanPaymentSuccess: Order not found', [
                    'txn_id' => $txnId,
                    'ip' => $request->ip(),
                ]);
                return response('Aborted', 400)->header('Content-Type', 'text/plain');
            }

            $statusText = $orderStatus === 'PAYMENT_ERROR' ? 'Failure' : 'Aborted';
            Log::warning('PhonePePlanPaymentSuccess: Payment not successful', [
                'txn_id' => $txnId,
                'order_status' => $orderStatus,
                'ip' => $request->ip(),
            ]);

            return response($statusText, 400)->header('Content-Type', 'text/plain');
        } catch (\Exception $e) {
            Log::error('PhonePePlanPaymentSuccess: General error', [
                'txn_id' => $txnId ?? null,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response('Aborted', 500)->header('Content-Type', 'text/plain');
        }
    }

     public function updateGroup(Request $request)
    {
        Log::info('updateGroup request', [
            'id' => $request->input('id'),
            'name' => $request->input('name'),
            'authentication_header' => $request->header('Authentication'),
            'ip' => $request->ip(),
        ]);

        // Validate inputs
        $token = $request->header('Authentication');
        $validator = Validator::make(array_merge($request->all(), ['Authentication' => $token]), [
            'id' => 'required|integer|exists:tbl_group,id',
            'name' => 'required|string|max:255',
            'Authentication' => 'required|string',
        ], [
            'Authentication.required' => 'Authentication token is required',
            'id.exists' => 'Invalid group ID',
        ]);

        if ($validator->fails()) {
            Log::warning('updateGroup: Validation failed', [
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

            Log::debug('updateGroup: Farmer query result', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'farmer_found' => $farmer ? true : false,
                'ip' => $request->ip(),
            ]);

            if (!$farmer) {
                Log::warning('updateGroup: Authentication failed', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Verify group belongs to farmer
            $group = Group::where('id', $request->input('id'))
                ->where('farmer_id', $farmer->id)
                ->where('is_active', 1)
                ->first();

            if (!$group) {
                Log::warning('updateGroup: Group not found or unauthorized', [
                    'group_id' => $request->input('id'),
                    'farmer_id' => $farmer->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Update group
            $group->name = $request->input('name');
            $updated = $group->save();

            if ($updated) {
                Log::info('updateGroup: Group updated successfully', [
                    'farmer_id' => $farmer->id,
                    'group_id' => $group->id,
                    'name' => $group->name,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'message' => 'Record Successfully Updated!',
                    'status' => 200,
                ], 200);
            } else {
                Log::warning('updateGroup: Group update failed', [
                    'farmer_id' => $farmer->id,
                    'group_id' => $group->id,
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('updateGroup: Database error', [
                'farmer_id' => $farmer->id ?? null,
                'group_id' => $request->input('id'),
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
            Log::error('updateGroup: General error', [
                'farmer_id' => $farmer->id ?? null,
                'group_id' => $request->input('id'),
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
