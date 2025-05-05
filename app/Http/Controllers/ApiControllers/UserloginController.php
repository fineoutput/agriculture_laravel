<?php

namespace App\Http\Controllers\ApiControllers;

use App\Models\Farmer;
use App\Models\Doctor;
use App\Models\Vendor;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

class UserloginController extends Controller
{
    /**
     * Test SMS sending functionality
     */
    public function msgtest()
    {
        $msg = 'आदरणीय डॉक्टर जी, आपका पंजीकरण सफल हुआ, DAIRY MUNEEM में आपका स्वागत है। कुछ देर में आप की आईडी एक्टिव हो जाएगी। व्हाट्सएप द्वारा हमसे जुड़ने के लिए क्लिक करें bit.ly/dairy_muneem। अधिक जानकारी के लिए 7891029090 पर कॉल करें। धन्यवाद! – DAIRY MUNEEM';
        $phone = '8387039990';
        $dlt = env('DLT_CODE', 'DEFAULT_DLT_CODE');

        $response = $this->sendSmsMsg91($phone, $msg, $dlt);

        return response()->json([
            'status' => true,
            'message' => 'SMS sent successfully',
            'response' => $response,
        ]);
    }

    /**
     * Farmer Registration Process
     */
    public function farmer_register_process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'village' => 'nullable|string',
            'district' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'pincode' => 'nullable|string',
            'phone' => 'required|string',
            'type' => 'required|string|in:farmer',
            'email' => 'nullable|email',
            'doc_type' => 'nullable|string',
            'degree' => 'nullable|string',
            'experience' => 'nullable|string',
            'shop_name' => 'nullable|string',
            'address' => 'nullable|string',
            'gst_no' => 'nullable|string',
            'aadhar_no' => 'nullable|string',
            'pan_no' => 'nullable|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'no_of_animals' => 'nullable|string',
            'expert_category' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'refer_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'image_' . date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('uploads/aadhar', $imageName, 'public');
            $imagePath = 'assets/' . $imagePath;
        }

        // Check if farmer exists
        $farmer = Farmer::where('phone', $request->phone)->first();
        if ($farmer) {
            return response()->json([
                'status' => false,
                'message' => 'Farmer Already Exist!',
            ], 400);
        }

        // Prepare data for OTP verification
        $farmerData = [
            'name' => $request->name,
            'village' => $request->village,
            'district' => $request->district,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'refer_code' => $request->refer_code,
            'phone' => $request->phone,
            'email' => $request->email,
            'image' => $imagePath,
            'doc_type' => $request->doc_type,
            'degree' => $request->degree,
            'experience' => $request->experience,
            'shop_name' => $request->shop_name,
            'address' => $request->address,
            'gst_no' => $request->gst_no,
            'aadhar_no' => $request->aadhar_no,
            'pan_no' => $request->pan_no,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'no_of_animals' => $request->no_of_animals,
            'expert_category' => $request->expert_category,
            'date' => now()->toDateTimeString(),
        ];

        try {
            $otp = rand(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            $otpRecord = Otp::create([
                'phone' => $request->phone,
                'otp' => $otp,
                'type' => $request->type,
                'data' => $farmerData,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);

            Log::info('OTP stored for farmer registration', [
                'phone' => $request->phone,
                'otp' => $otp,
                'otp_record_id' => $otpRecord->id,
            ]);

            $msg = "Your OTP for DAIRY MUNEEM registration is: $otp. Valid for 10 minutes.";
            $this->sendSmsMsg91($request->phone, $msg, env('DLT_CODE', '645ca6f9d6fc057295695743'));

            Log::info('OTP sent for farmer registration', ['phone' => $request->phone]);

            return response()->json([
                'status' => true,
                'message' => 'Please enter OTP sent to your registered mobile number',
                'data' => ['phone' => $request->phone],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in farmer_register_process', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error during registration: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Farmer Register OTP Verify
     */
    public function farmer_register_otp_verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $otpRecord = Otp::where('phone', $request->phone)
                ->where('otp', $request->otp)
                ->where('type', 'farmer')
                ->where('expires_at', '>', now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }

            $farmerData = $otpRecord->data;

            $farmer = new Farmer();
            $farmer->name = $farmerData['name'];
            $farmer->village = $farmerData['village'];
            $farmer->district = $farmerData['district'];
            $farmer->city = $farmerData['city'];
            $farmer->state = $farmerData['state'];
            $farmer->pincode = $farmerData['pincode'];
            $farmer->refer_code = $farmerData['refer_code'];
            $farmer->phone = $farmerData['phone'];
            $farmer->email = $farmerData['email'];
            $farmer->image = $farmerData['image'];
            $farmer->doc_type = $farmerData['doc_type'];
            $farmer->degree = $farmerData['degree'];
            $farmer->experience = $farmerData['experience'];
            $farmer->shop_name = $farmerData['shop_name'];
            $farmer->address = $farmerData['address'];
            $farmer->gst_no = $farmerData['gst_no'];
            $farmer->aadhar_no = $farmerData['aadhar_no'];
            $farmer->pan_no = $farmerData['pan_no'];
            $farmer->type = $farmerData['type'];
            $farmer->latitude = $farmerData['latitude'];
            $farmer->longitude = $farmerData['longitude'];
            $farmer->no_of_animals = $farmerData['no_of_animals'];
            $farmer->expert_category = $farmerData['expert_category'];

            if ($farmer->save()) {
                $otpRecord->delete();

                $msg = "आदरणीय {$farmerData['name']} जी, आपका पंजीकरण सफल हुआ, DAIRY MUNEEM में आपका स्वागत है। कुछ देर में आप की आईडी एक्टिव हो जाएगी। व्हाट्सएप द्वारा हमसे जुड़ने के लिए क्लिक करें bit.ly/dairy_muneem। अधिक जानकारी के लिए 7891029090 पर कॉल करें। धन्यवाद! – DAIRY MUNEEM";
                $this->sendSmsMsg91($farmerData['phone'], $msg, env('DLT_CODE', '645ca6f9d6fc057295695743'));

                $token = JWTAuth::fromUser($farmer);
                $farmer->auth = $token;
                $farmer->save();
                Log::info('Farmer registered successfully', [
                    'phone' => $farmerData['phone'],
                    'farmer_id' => $farmer->id,
                    'token' => $token,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Farmer registered successfully',
                    'farmer_id' => $farmer->id,
                    'token' => $token,
                ], 200);
            } else {
                Log::error('Failed to save farmer data', [
                    'phone' => $farmerData['phone'],
                ]);
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save farmer data',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in farmer_register_otp_verify', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error during OTP verification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Farmer Login Process
     */
    public function farmer_login_process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $farmer = Farmer::where('phone', $request->phone)->first();
            if (!$farmer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Farmer not found',
                ], 404);
            }

            $otp = rand(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            $otpRecord = Otp::create([
                'phone' => $request->phone,
                'otp' => $otp,
                'type' => 'farmer',
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);

            Log::info('OTP stored for farmer login', [
                'phone' => $request->phone,
                'otp' => $otp,
                'otp_record_id' => $otpRecord->id,
            ]);

            $msg = "Your OTP for DAIRY MUNEEM login is: $otp. Valid for 10 minutes.";
            $this->sendSmsMsg91($request->phone, $msg, env('DLT_CODE', '645ca6f9d6fc057295695743'));

            Log::info('OTP sent for farmer login', ['phone' => $request->phone]);

            return response()->json([
                'status' => true,
                'message' => 'OTP sent for farmer login',
                'data' => ['phone' => $request->phone],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in farmer_login_process', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error during login: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Farmer Login OTP Verify
     */
    public function farmer_login_otp_verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $otpRecord = Otp::where('phone', $request->phone)
                ->where('otp', $request->otp)
                ->where('type', 'farmer')
                ->where('expires_at', '>', now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }

            $farmer = Farmer::where('phone', $request->phone)->where('type', 'farmer')->first();
            if (!$farmer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Farmer not found',
                ], 404);
            }

            Auth::guard('farmer')->login($farmer);

            $token = JWTAuth::fromUser($farmer);
            $farmer->auth = $token;
            $farmer->save();
            $otpRecord->delete();

            Log::info('Farmer login verified', [
                'phone' => $request->phone,
                'farmer_id' => $farmer->id,
                'token' => $token,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Farmer login verified successfully',
                'farmer_id' => $farmer->id,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in farmer_login_otp_verify', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error during OTP verification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Doctor/Vendor Registration Process
     */
    public function registerWithOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'type' => 'required|string|in:doctor,vendor',
            'village' => 'nullable|string',
            'district' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'pincode' => 'nullable|string',
            'refer_code' => 'nullable|string',
            'email' => 'nullable|email',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'doc_type' => 'nullable|string',
            'degree' => 'nullable|string',
            'experience' => 'nullable|string',
            'shop_name' => 'nullable|string',
            'address' => 'nullable|string',
            'gst_no' => 'nullable|string',
            'aadhar_no' => 'nullable|string',
            'pan_no' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'no_of_animals' => 'nullable|integer',
            'expert_category' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'image_' . date('YmdHis') . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('uploads/aadhar', $imageName, 'public');
            $imagePath = 'assets/' . $imagePath;
        }

        try {
            $phone = $request->phone;
            $type = $request->type;

            // Check if user exists
            $farmer = Farmer::where('phone', $phone)->first();
            if ($farmer) {
                return response()->json([
                    'status' => false,
                    'message' => 'User Already Exist!',
                ], 400);
            }

            $doctor = Doctor::where('phone', $phone)->first();
            if ($doctor) {
                return response()->json([
                    'status' => false,
                    'message' => 'User Already Exist!',
                ], 400);
            }

            $vendor = Vendor::where('phone', $phone)->first();
            if ($vendor) {
                return response()->json([
                    'status' => false,
                    'message' => 'User Already Exist!',
                ], 400);
            }

            // Prepare data for OTP table
            $data = [
                'name' => $request->name,
                'village' => $request->village,
                'district' => $request->district,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'refer_code' => $request->refer_code,
                'phone' => $phone,
                'email' => $request->email,
                'image' => $imagePath,
                'doc_type' => $request->doc_type,
                'degree' => $request->degree,
                'experience' => $request->experience,
                'shop_name' => $request->shop_name,
                'address' => $request->address,
                'gst_no' => $request->gst_no,
                'aadhar_no' => $request->aadhar_no,
                'pan_no' => $request->pan_no,
                'type' => $type,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'no_of_animals' => $request->no_of_animals,
                'expert_category' => $request->expert_category,
                'date' => now()->toDateTimeString(),
            ];

            $otp = rand(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            $otpRecord = Otp::create([
                'phone' => $phone,
                'otp' => $otp,
                'type' => $type,
                'data' => $data,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);

            Log::info('OTP stored for registration', [
                'phone' => $phone,
                'otp' => $otp,
                'otp_record_id' => $otpRecord->id,
            ]);

            $msg = "Your OTP for DAIRY MUNEEM registration is: $otp. Valid for 10 minutes.";
            $this->sendSmsMsg91($phone, $msg, env('DLT_CODE', '645ca6f9d6fc057295695743'));

            Log::info('OTP sent for registration', ['phone' => $phone]);

            return response()->json([
                'status' => true,
                'message' => 'Please enter OTP sent to your registered mobile number',
                'data' => ['phone' => $phone],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in registerWithOtp', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Some error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Doctor/Vendor Register OTP Verify
     */
    public function register_otp_verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $otpRecord = Otp::where('phone', $request->phone)
                ->where('otp', $request->otp)
                ->where('expires_at', '>', now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }

            $userData = $otpRecord->data;
            $type = $userData['type'];

            $modelMap = [
                'doctor' => Doctor::class,
                'vendor' => Vendor::class,
            ];

            if (!isset($modelMap[$type])) {
                throw new \Exception('Invalid user type');
            }

            $model = new $modelMap[$type]();

            $model->name = $userData['name'];
            $model->village = $userData['village'];
            $model->district = $userData['district'];
            $model->city = $userData['city'];
            $model->state = $userData['state'];
            $model->pincode = $userData['pincode'];
            $model->refer_code = $userData['refer_code'];
            $model->phone = $userData['phone'];
            $model->email = $userData['email'];
            $model->image = $userData['image'];
            $model->doc_type = $userData['doc_type'];
            $model->degree = $userData['degree'];
            $model->experience = $userData['experience'];
            $model->shop_name = $userData['shop_name'];
            $model->address = $userData['address'];
            $model->gst_no = $userData['gst_no'];
            $model->aadhar_no = $userData['aadhar_no'];
            $model->pan_no = $userData['pan_no'];
            $model->type = $userData['type'];
            $model->latitude = $userData['latitude'];
            $model->longitude = $userData['longitude'];
            $model->no_of_animals = $userData['no_of_animals'];
            $model->expert_category = $userData['expert_category'];
            $model->is_approved = 0;

            if ($model->save()) {
                $otpRecord->delete();

                $msg = "आदरणीय {$userData['name']} जी, आपका पंजीकरण सफल हुआ, DAIRY MUNEEM में आपका स्वागत है। कुछ देर में आप की आईडी एक्टिव हो जाएगी। व्हाट्सएप द्वारा हमसे जुड़ने के लिए क्लिक करें bit.ly/dairy_muneem। अधिक जानकारी के लिए 7891029090 पर कॉल करें। धन्यवाद! – DAIRY MUNEEM";
                $this->sendSmsMsg91($userData['phone'], $msg, env('DLT_CODE', '645ca6f9d6fc057295695743'));

                $token = JWTAuth::fromUser($model);
                $model->auth = $token;
                $model->save();
                Log::info('User registered successfully', [
                    'phone' => $userData['phone'],
                    'user_id' => $model->id,
                    'type' => $type,
                    'token' => $token,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Registration verified successfully',
                    'user_id' => $model->id,
                    'token' => $token,
                ], 200);
            } else {
                Log::error('Failed to save user data', ['phone' => $userData['phone'], 'type' => $type]);
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to save user data',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in register_otp_verify', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error during OTP verification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Doctor/Vendor Login Process
     */
    public function login_process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'type' => 'required|string|in:doctor,vendor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $phone = $request->phone;
            $type = $request->type;

            $modelMap = [
                'doctor' => Doctor::class,
                'vendor' => Vendor::class,
            ];

            if (!isset($modelMap[$type])) {
                throw new \Exception('Invalid user type');
            }

            $model = $modelMap[$type];

            $user = $model::where('phone', $phone)->where('type', $type)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $otp = rand(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            $otpRecord = Otp::create([
                'phone' => $phone,
                'otp' => $otp,
                'type' => $type,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);

            Log::info('OTP stored for login', [
                'phone' => $phone,
                'otp' => $otp,
                'type' => $type,
                'otp_record_id' => $otpRecord->id,
            ]);

            $msg = "Your OTP for DAIRY MUNEEM login is: $otp. Valid for 10 minutes.";
            $this->sendSmsMsg91($phone, $msg, env('DLT_CODE', '645ca6f9d6fc057295695743'));

            Log::info('OTP sent for user login', ['phone' => $phone, 'type' => $type]);

            return response()->json([
                'status' => true,
                'message' => 'OTP sent for login',
                'data' => ['phone' => $phone],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in login_process', [
                'phone' => $request->phone,
                'type' => $request->type,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error during login: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Doctor/Vendor Login OTP Verify
     */
    public function login_otp_verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
            'type' => 'required|string|in:doctor,vendor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $otpRecord = Otp::where('phone', $request->phone)
                ->where('otp', $request->otp)
                ->where('type', $request->type)
                ->where('expires_at', '>', now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }

            $modelMap = [
                'doctor' => Doctor::class,
                'vendor' => Vendor::class,
            ];

            if (!isset($modelMap[$request->type])) {
                throw new \Exception('Invalid user type');
            }

            $model = $modelMap[$request->type];

            $user = $model::where('phone', $request->phone)->where('type', $request->type)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $guardMap = [
                'doctor' => 'doctor',
                'vendor' => 'vendor',
            ];

            if (!isset($guardMap[$request->type])) {
                throw new \Exception('Invalid user type');
            }

            $guard = $guardMap[$request->type];

            Log::info('Guard assigned', [
                'phone' => $request->phone,
                'type' => $request->type,
                'guard' => $guard,
            ]);

            Auth::guard($guard)->login($user);

            $token = JWTAuth::fromUser($user);
            $user->auth = $token;
            $user->save();
            $otpRecord->delete();

            Log::info('User login verified', [
                'phone' => $request->phone,
                'type' => $request->type,
                'user_id' => $user->id,
                'guard' => $guard,
                'token' => $token,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Login verified successfully',
                'user_id' => $user->id,
                'token' => $token,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in login_otp_verify', [
                'phone' => $request->phone,
                'type' => $request->type,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error during OTP verification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update User Profile
     */
    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string',
            'lname' => 'required|string',
            'type' => 'required|string|in:farmer,doctor,vendor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $modelMap = [
                'farmer' => Farmer::class,
                'doctor' => Doctor::class,
                'vendor' => Vendor::class,
            ];

            if (!isset($modelMap[$request->type]) || $user->type !== $request->type) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid user type',
                ], 400);
            }

            $model = $modelMap[$request->type];
            $updated = $model::where('id', $user->id)->update([
                'f_name' => $request->fname,
                'l_name' => $request->lname,
            ]);

            if ($updated) {
                Log::info('User profile updated', ['user_id' => $user->id, 'type' => $request->type]);
                return response()->json([
                    'status' => true,
                    'message' => 'Profile updated successfully',
                ], 200);
            } else {
                Log::error('Failed to update user profile', ['user_id' => $user->id, 'type' => $request->type]);
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update profile',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in update_profile', [
                'user_id' => auth('api')->id(),
                'type' => $request->type,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error during profile update: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * User Logout
     */
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            Log::info('User logged out', ['user_id' => auth('api')->id()]);
            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in logout', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Error during logout: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send SMS using Msg91
     */
    private function sendSmsMsg91($phone, $message, $dlt)
    {
        try {
            Log::info('Mock SMS sent', [
                'phone' => $phone,
                'message' => $message,
                'dlt' => $dlt,
            ]);

            preg_match('/is: (\d{6})/', $message, $matches);
            $otp = isset($matches[1]) ? $matches[1] : 'Unknown';

            Log::info('Stored OTP for testing', [
                'phone' => $phone,
                'otp' => $otp,
            ]);

            return [
                'type' => 'success',
                'message' => 'Mock SMS sent successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Mock SMS sending failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}