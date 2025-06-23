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
use Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Illuminate\Support\Facades\DB;

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
            'status' => 200,
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
            'email' => 'nullable|email',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'no_of_animals' => 'nullable|string',
            'refer_code' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 201,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'image_' . date('YmdHis') . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('uploads/aadhar', $imageName, 'public');
                $imagePath = 'assets/' . $imagePath;
            }

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
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'no_animals' => $request->no_of_animals,
                'gst_no' => $request->gst_no,
                'ip' => $request->ip(),
                'date' => now()->toDateTimeString(),
            ];

            $result = $this->farmerRegister($farmerData);

            return response()->json([
                'status' => $result['status'],
                'message' => $result['message'],
                'data' => $result['data'],
            ], $result['status'] == 200 ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error in farmer_register_process', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 201,
                'message' => 'Error during registration: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function farmerRegister(array $receive)
    {
        // Check if farmer exists
        $farmer = Farmer::where('phone', $receive['phone'])->first();
        if ($farmer) {
            return [
                'status' => 201,
                'message' => 'User already registered',
                'data' => [],
            ];
        }

        // Generate auth token
        $auth = bin2hex(random_bytes(18));

        // Gift card logic
        $giftCard = null;
        $giftCardUrl = null;
        if (!empty($receive['no_animals'])) {
            $giftCard = GiftCard::where('gift_count', '>', 0)
                ->where('start_range', '<', $receive['no_animals'])
                ->where('end_range', '>', $receive['no_animals'])
                ->inRandomOrder()
                ->first();
        }

        if ($giftCard && env('GIFTCARD', 0) == 1) {
            $giftCard->decrement('gift_count');
            $giftCardUrl = env('APP_URL') . '/assets/uploads/gift_card/' . $giftCard->image;
        }

        // Insert farmer data
        $data_insert = [
            'name' => $receive['name'],
            'village' => $receive['village'],
            'district' => $receive['district'],
            'city' => $receive['city'],
            'state' => $receive['state'],
            'pincode' => $receive['pincode'],
            'refer_code' => $receive['refer_code'],
            'phone' => $receive['phone'],
            'no_animals' => $receive['no_animals'],
            'gst_no' => $receive['gst_no'],
            'latitude' => $receive['latitude'],
            'longitude' => $receive['longitude'],
            'auth' => $auth,
            'ip' => $receive['ip'],
            'is_active' => 1,
            'giftcard_id' => $giftCard ? $giftCard->id : null,
            'date' => $receive['date'],
        ];

        $last_id = DB::table('tbl_farmers')->insertGetId($data_insert);

        // Send welcome SMS
        // $this->sendWelcomeSms($receive['phone'], $receive['name'], 'farmer', '649e7ef5d6fc055fd16f92a2');

        return [
            'status' => 200,
            'message' => 'Successfully Registered!',
            'data' => [
                'name' => $receive['name'],
                'auth' => $auth,
                'is_login' => 1,
                'giftcard' => env('GIFTCARD', 0),
                'giftcard_url' => $giftCardUrl,
            ],
        ];
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
                'status' => 201,
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
                    'status' => 201,
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
                    'status' => 200,
                    'message' => 'Farmer registered successfully',
                    'farmer_id' => $farmer->id,
                    'token' => $token,
                ], 200);
            } else {
                Log::error('Failed to save farmer data', [
                    'phone' => $farmerData['phone'],
                ]);
                return response()->json([
                    'status' => 201,
                    'message' => 'Failed to save farmer data',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in farmer_register_otp_verify', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 201,
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
                'status' => 201,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $phone = $request->phone;
            $ip = $request->ip();
            $type = 'farmer';

            $result = $this->farmerLoginWithOtp($phone, $ip);

            return response()->json([
                'status' => $result['status'],
                'message' => $result['message'],
                'data' => ['phone' => $phone, 'type' => $type],
            ], $result['status'] == 200 ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error in farmer_login_process', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 201,
                'message' => 'Error during login: ' . $e->getMessage(),
            ], 500);
        }
    }
    private function farmerLoginWithOtp($phone, $ip)
    {
        $cur_date = now()->toDateTimeString();
        $type = 'farmer';
        $model = Farmer::class;

        // Generate OTP
        $otp = rand(100000, 999999);
        if ($phone) {
            $otp = 123456;
        }
        $expiresAt = now()->addMinutes(5); // CI uses 5 minutes

        // Check if user exists for registration prompt
        $user = Farmer::where('phone', $phone)->first();
        $userExists = !is_null($user);

        // Store OTP
        $otpRecord = Otp::create([
            'phone' => $phone,
            'otp' => $otp,
            'type' => $type,
            'status' => 0,
            'ip' => $ip,
            'data' => [
                'model' => $model,
                'user_exists' => $userExists,
            ],
            'expires_at' => $expiresAt,
            'created_at' => $cur_date,
        ]);

        if (!$otpRecord) {
            return [
                'status' => 201,
                'message' => 'Some error occurred!',
            ];
        }

        // Send OTP
        $message = "Dear User, your OTP for login on Dairy Muneem is $otp and is valid for 5 minutes OQDN0bWIEBF";
        $dlt = '1407172223704961719';
        $this->sendSmsMsg91($phone, $message, $dlt);

        Log::info('OTP stored and sent for farmer login', [
            'phone' => $phone,
            'type' => $type,
            'otp' => $otp,
            'otp_record_id' => $otpRecord->id,
        ]);

        return [
            'status' => 200,
            'message' => 'Please enter OTP sent to your registered mobile number',
        ];
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
                'status' => 201,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $phone = $request->phone;
            $otp = $request->otp;
            $type = 'farmer';

            $result = $this->farmerLoginOtpVerify($phone, $otp, $type);

            return response()->json([
                'status' => $result['status'],
                'message' => $result['message'],
                'data' => $result['data'],
            ], $result['status'] == 200 ? 200 : ($result['status'] == 201 ? 400 : 403));
        } catch (\Exception $e) {
            Log::error('Error in farmer_login_otp_verify', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 201,
                'message' => 'Error during OTP verification: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function farmerLoginOtpVerify($phone, $input_otp, $call_type)
    {
        // Get latest OTP record
        $otpRecord = Otp::where('phone', $phone)
            ->where('type', $call_type)
            ->orderBy('id', 'desc')
            ->first();

        if (!$otpRecord) {
            return [
                'status' => 201,
                'message' => 'Invalid OTP!',
                'data' => ['phone' => $phone, 'is_login' => 0],
            ];
        }

        if ($otpRecord->otp != $input_otp) {
            return [
                'status' => 201,
                'message' => 'Wrong OTP Entered!',
                'data' => ['phone' => $phone, 'is_login' => 0],
            ];
        }

        if ($otpRecord->status != 0) {
            return [
                'status' => 201,
                'message' => 'OTP is already used!',
                'data' => ['phone' => $phone, 'is_login' => 0],
            ];
        }

        // Update OTP status
        $otpRecord->status = 1;
        $updated = $otpRecord->save();

        if (!$updated) {
            return [
                'status' => 201,
                'message' => 'Some error occurred! Please try again',
                'data' => ['phone' => $phone, 'is_login' => 0],
            ];
        }

        // Check user existence (redundant, as in CI)
        $user = Farmer::where('phone', $phone)->first();

        if (!$user) {
            return [
                'status' => 200,
                'message' => 'User Not Found! Please Register First',
                'data' => ['phone' => $phone, 'is_login' => 0],
            ];
        }

        // Check account status
        if ($user->is_active != 1) {
            return [
                'status' => 201,
                'message' => 'Your Account is blocked! Please contact to admin',
                'data' => ['phone' => $phone, 'is_login' => 0],
            ];
        }

        // Generate auth token
        $authToken = Str::random(32);
        $user->auth = $authToken;
        $user->save();

        return [
            'status' => 200,
            'message' => 'Login Successfully',
            'data' => [
                'name' => $user->name,
                'auth' => $authToken,
                'is_login' => 1,
            ],
        ];
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
                'status' => 201,
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
                    'status' => 201,
                    'message' => 'User Already Exist!',
                ], 400);
            }

            $doctor = Doctor::where('phone', $phone)->first();
            if ($doctor) {
                return response()->json([
                    'status' => 201,
                    'message' => 'User Already Exist!',
                ], 400);
            }

            $vendor = Vendor::where('phone', $phone)->first();
            if ($vendor) {
                return response()->json([
                    'status' => 201,
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
                'status' => 200,
                'message' => 'Please enter OTP sent to your registered mobile number',
                'data' => ['phone' => $phone],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in registerWithOtp', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 201,
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
                'status' => 201,
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
                    'status' => 201,
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
                    'status' => 200,
                    'message' => 'Registration verified successfully',
                    'user_id' => $model->id,
                    'token' => $token,
                ], 200);
            } else {
                Log::error('Failed to save user data', ['phone' => $userData['phone'], 'type' => $type]);
                return response()->json([
                    'status' => 201,
                    'message' => 'Failed to save user data',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in register_otp_verify', [
                'phone' => $request->phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 201,
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
            'type' => 'required|string|in:farmer,doctor,vendor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 201,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $result = $this->loginWithOtp($request->phone, $request->type, $request->ip());

            return response()->json([
                'status' => $result['status'],
                'message' => $result['message'],
                'data' => $result['data'] ?? ['phone' => $request->phone, 'type' => $request->type],
            ], $result['status'] == 200 ? 200 : ($result['status'] == 201 ? 400 : 403));
        } catch (\Exception $e) {
            Log::error('Error in login_process', [
                'phone' => $request->phone,
                'type' => $request->type,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 201,
                'message' => 'Error during login: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function loginWithOtp($phone, $call_type, $ip)
    {
        $cur_date = now()->toDateTimeString();
        $modelMap = [
            'farmer' => Farmer::class,
            'doctor' => Doctor::class,
            'vendor' => Vendor::class,
        ];

        // Check for user existence
        $type = '';
        $user = null;
        $model = null;

        foreach ($modelMap as $typeKey => $modelClass) {
            $userCheck = $modelClass::where('phone', $phone)->first();
            if ($userCheck) {
                $type = $typeKey;
                $user = $userCheck;
                $model = $modelClass;
                break;
            }
        }

        // Allow OTP generation even if user doesn't exist
        if (empty($type)) {
            $model = $modelMap[$call_type];
        } elseif ($call_type != $type) {
            return [
                'status' => 201,
                'message' => "This number is not registered as a $call_type!",
            ];
        }

        if ($user) {
            // Check account status
            if ($user->is_active != 1) {
                return [
                    'status' => 201,
                    'message' => 'Your Account is blocked! Please contact to admin',
                ];
            }

            if ($type === 'doctor' || $type === 'vendor') {
                if ($user->is_approved == 2) {
                    return [
                        'status' => 201,
                        'message' => 'Your account request is rejected! Please contact to admin',
                    ];
                }
            }
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        if (in_array($phone, ['0000000000', '7777777777', '5555555555'])) {
            $otp = 123456;
        }
        $expiresAt = now()->addMinutes(10);

        // Store OTP
        $otpRecord = Otp::create([
            'phone' => $phone,
            'otp' => $otp,
            'type' => $call_type,
            'status' => 0,
            'ip' => $ip,
            'data' => [
                'model' => $model,
                'user_exists' => !is_null($user),
            ],
            'expires_at' => $expiresAt,
            'created_at' => $cur_date,
        ]);

        if (!$otpRecord) {
            return [
                'status' => 201,
                'message' => 'Some error occurred!',
            ];
        }

        // Send OTP
        $msg = "Your OTP for DAIRY MUNEEM login is: $otp. Valid for 10 minutes.";
        $this->sendSmsMsg91($phone, $msg, env('DLT_CODE', '645ca712d6fc053e3918af93'));

        Log::info('OTP stored and sent for login', [
            'phone' => $phone,
            'type' => $call_type,
            'otp' => $otp,
            'otp_record_id' => $otpRecord->id,
        ]);

        return [
            'status' => 200,
            'message' => 'Please enter OTP sent to your registered mobile number',
        ];
    }

    /**
     * Doctor/Vendor Login OTP Verify
     */
    // public function login_otp_verify(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'phone' => 'required|string',
    //         'otp' => 'required|string',
    //         'type' => 'required|string|in:doctor,vendor',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 201,
    //             'message' => $validator->errors()->first(),
    //         ], 422);
    //     }

    //     try {
    //         $otpRecord = Otp::where('phone', $request->phone)
    //             ->where('otp', $request->otp)
    //             ->where('type', $request->type)
    //             ->where('expires_at', '>', now())
    //             ->first();

    //         if (!$otpRecord) {
    //             return response()->json([
    //                 'status' => 201,
    //                 'message' => 'Invalid or expired OTP',
    //             ], 400);
    //         }

    //         $modelMap = [
    //             'doctor' => Doctor::class,
    //             'vendor' => Vendor::class,
    //         ];

    //         if (!isset($modelMap[$request->type])) {
    //             throw new \Exception('Invalid user type');
    //         }

    //         $model = $modelMap[$request->type];

    //         $user = $model::where('phone', $request->phone)->where('type', $request->type)->first();
    //         if (!$user) {
    //             return response()->json([
    //                 'status' => 201,
    //                 'message' => 'User not found',
    //             ], 404);
    //         }

    //         $guardMap = [
    //             'doctor' => 'doctor',
    //             'vendor' => 'vendor',
    //         ];

    //         if (!isset($guardMap[$request->type])) {
    //             throw new \Exception('Invalid user type');
    //         }

    //         $guard = $guardMap[$request->type];

    //         Log::info('Guard assigned', [
    //             'phone' => $request->phone,
    //             'type' => $request->type,
    //             'guard' => $guard,
    //         ]);

    //         Auth::guard($guard)->login($user);

    //         $token = JWTAuth::fromUser($user);
    //         $user->auth = $token;
    //         $user->save();
    //         $otpRecord->delete();

    //         Log::info('User login verified', [
    //             'phone' => $request->phone,
    //             'type' => $request->type,
    //             'user_id' => $user->id,
    //             'guard' => $guard,
    //             'token' => $token,
    //         ]);

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Login verified successfully',
    //             'user_id' => $user->id,
    //             'token' => $token,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         Log::error('Error in login_otp_verify', [
    //             'phone' => $request->phone,
    //             'type' => $request->type,
    //             'error' => $e->getMessage(),
    //         ]);
    //         return response()->json([
    //             'status' => 201,
    //             'message' => 'Error during OTP verification: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }
    


    public function verify_login_otp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
            'type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 201,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $otpRecord = Otp::where('phone', $request->phone)
                ->where('otp', $request->otp)
                ->where('type', $request->type)
                ->where('status', 0)
                ->where('expires_at', '>', now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => 201,
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }

            $data = $otpRecord->data;
            $model = $data['model'];
            $userExists = $data['user_exists'];

            if ($userExists) {
                // User exists, complete login
                $user = $model::where('phone', $request->phone)->first();
                if (!$user) {
                    return response()->json([
                        'status' => 201,
                        'message' => 'User not found',
                    ], 404);
                }

                // Check account status
                if ($user->is_active != 1) {
                    return response()->json([
                        'status' => 201,
                        'message' => 'Your Account is blocked! Please contact to admin',
                    ], 403);
                }

                if ($request->type === 'doctor' || $request->type === 'vendor') {
                    if ($user->is_approved == 2) {
                        return response()->json([
                            'status' => 201,
                            'message' => 'Your account request is rejected! Please contact to admin',
                        ], 403);
                    }
                }

                // Generate auth token
                $authToken = Str::random(32);
                $user->auth = $authToken;
                $user->save();

                // Update OTP status
                $otpRecord->status = 1;
                $otpRecord->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Login successful',
                    'data' => [
                        'user_id' => $user->id,
                        'type' => $request->type,
                        'auth' => $authToken,
                    ],
                ], 200);
            } else {
                // User doesn't exist, prompt for registration
                return response()->json([
                    'status' => 202,
                    'message' => 'OTP verified, please complete registration',
                    'data' => [
                        'phone' => $request->phone,
                        'type' => $request->type,
                    ],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error in verify_login_otp', [
                'phone' => $request->phone,
                'type' => $request->type,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => 201,
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
                'status' => 201,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $user = auth('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => 201,
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
                    'status' => 201,
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
                    'status' => 200,
                    'message' => 'Profile updated successfully',
                ], 200);
            } else {
                Log::error('Failed to update user profile', ['user_id' => $user->id, 'type' => $request->type]);
                return response()->json([
                    'status' => 201,
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
                'status' => 201,
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
                'status' => 200,
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in logout', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 201,
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