<?php

namespace App\Http\Controllers\ApiControllers;

use App\Models\Farmer;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Doctor;
use App\Models\tbl_otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RequestFacade;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

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
            'name'            => 'required|string',
            'village'         => 'nullable|string',
            'district'        => 'nullable|string',
            'city'            => 'nullable|string',
            'state'           => 'nullable|string',
            'pincode'         => 'nullable|string',
            'phone'           => 'required|string',
            'type'            => 'required|string',
            'email'           => 'nullable|email',
            'doc_type'        => 'nullable|string',
            'degree'          => 'nullable|string',
            'experience'      => 'nullable|string',
            'shop_name'       => 'nullable|string',
            'address'         => 'nullable|string',
            'gst_no'          => 'nullable|string',
            'aadhar_no'       => 'nullable|string',
            'pan_no'          => 'nullable|string',
            'latitude'        => 'required|string',
            'longitude'       => 'required|string',
            'no_of_animals'   => 'nullable|string',
            'expert_category' => 'nullable|string',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            'refer_code'      => 'nullable|string',
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

        // Prepare data for OTP verification
        $farmerData = [
            'name'            => $request->name,
            'village'         => $request->village,
            'district'        => $request->district,
            'city'            => $request->city,
            'state'           => $request->state,
            'pincode'         => $request->pincode,
            'refer_code'      => $request->refer_code,
            'phone'           => $request->phone,
            'email'           => $request->email,
            'image'           => $imagePath,
            'doc_type'        => $request->doc_type,
            'degree'          => $request->degree,
            'experience'      => $request->experience,
            'shop_name'       => $request->shop_name,
            'address'         => $request->address,
            'gst_no'          => $request->gst_no,
            'aadhar_no'       => $request->aadhar_no,
            'pan_no'          => $request->pan_no,
            'type'            => $request->type,
            'latitude'        => $request->latitude,
            'longitude'       => $request->longitude,
            'no_of_animals'   => $request->no_of_animals,
            'expert_category' => $request->expert_category,
        ];

        try {
            $otpResponse = $this->farmerRegisterWithOtpVerify($farmerData);
            return response()->json($otpResponse);
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
        $otpRecord = tbl_otp::where('phone', $request->phone)
            ->where('otp', $request->otp)
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
            // Use Eloquent Model instead of DB::table to delete OTP
            tbl_otp::where('id', $otpRecord->id)->delete();

            $msg = "आदरणीय {$farmerData['name']} जी, आपका पंजीकरण सफल हुआ, DAIRY MUNEEM में आपका स्वागत है। कुछ देर में आप की आईडी एक्टिव हो जाएगी। व्हाट्सएप द्वारा हमसे जुड़ने के लिए क्लिक करें bit.ly/dairy_muneem। अधिक जानकारी के लिए 7891029090 पर कॉल करें। धन्यवाद! – DAIRY MUNEEM";
            $this->sendSmsMsg91($farmerData['phone'], $msg, env('DLT_CODE'));

            Log::info('Farmer registered successfully', [
                'phone' => $farmerData['phone'],
                'farmer_id' => $farmer->id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Farmer registered successfully',
                'farmer_id' => $farmer->id,
            ]);
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

            // Use Otp model to store OTP
            $otpRecord = tbl_otp::create([
                'phone' => $request->phone,
                'otp' => $otp,
                'type' => $farmer->type,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);

            Log::info('OTP insert attempted', [
                'phone' => $request->phone,
                'otp' => $otp,
                'otp_record_id' => $otpRecord->id,
            ]);

            $msg = "Your OTP for DAIRY MUNEEM login is: $otp. Valid for 10 minutes.";
            $this->sendSmsMsg91($request->phone, $msg, env('DLT_CODE', 'DEFAULT_DLT_CODE'));

            Log::info('OTP sent for farmer login', ['phone' => $request->phone]);

            return response()->json([
                'status' => true,
                'message' => 'OTP sent for farmer login',
                'data' => ['phone' => $request->phone,
                            'name' => $request->name],
            ]);
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
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $otpRecord = DB::table('tbl_otp')
                ->where('phone', $request->phone)
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

            $farmer = Farmer::where('phone', $request->phone)->where('type', $request->type)->first();
            if (!$farmer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Farmer not found',
                ], 404);
            }

            // Log in the farmer (custom logic or use Auth)
            // Auth::guard('farmer')->login($farmer);

            DB::table('tbl_otp')->where('id', $otpRecord->id)->delete();

            Log::info('Farmer login verified', ['phone' => $request->phone, 'farmer_id' => $farmer->id]);

            return response()->json([
                'status' => true,
                'message' => 'Farmer login verified successfully',
                'farmer_id' => $farmer->id,
            ]);
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
            'type' => 'required|string|in:farmer,doctor,vendor',
            'village' => 'nullable|string',
            'district' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'pincode' => 'nullable|string',
            'refer_code' => 'nullable|string',
            'email' => 'nullable|email',
            'image' => 'nullable|string',
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

        try {
            $phone = $request->phone;
            $type = $request->type;

            // Check if user exists in farmers, doctors, or vendors
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
                'image' => $request->image,
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
                // 'ip' => RequestFacade::ip(),
                'date' => now()->toDateTimeString(),
            ];

            $otp = rand(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            // Store OTP and data
            $otpRecord = tbl_otp::create([
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
            $otpRecord = tbl_otp::where('phone', $request->phone)
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

            // Determine model based on type
            switch ($type) {
                case 'farmer':
                    $model = new Farmer();
                    break;
                case 'doctor':
                    $model = new Doctor();
                    break;
                case 'vendor':
                    $model = new Vendor();
                    break;
                default:
                    throw new \Exception('Invalid user type');
            }
            
            

            // Assign data to model
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

            if ($model->save()) {
                $otpRecord->delete();

                $msg = "आदरणीय {$userData['name']} जी, आपका पंजीकरण सफल हुआ, DAIRY MUNEEM में आपका स्वागत है। कुछ देर में आप की आईडी एक्टिव हो जाएगी। व्हाट्सएप द्वारा हमसे जुड़ने के लिए क्लिक करें bit.ly/dairy_muneem। अधिक जानकारी के लिए 7891029090 पर कॉल करें। धन्यवाद! – DAIRY MUNEEM";
                $this->sendSmsMsg91($userData['phone'], $msg, env('DLT_CODE', '645ca6f9d6fc057295695743'));

                Log::info('User registered successfully', [
                    'phone' => $userData['phone'],
                    'user_id' => $model->id,
                    'type' => $type,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Registration verified successfully',
                    'user_id' => $model->id,
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
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $user = User::where('phone', $request->phone)->where('type', $request->type)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $otp = rand(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            DB::table('tbl_otp')->insert([
                'phone' => $request->phone,
                'otp' => $otp,
                'type' => $request->type,
                'expires_at' => $expiresAt,
                'created_at' => now(),
            ]);

            $msg = "Your OTP for DAIRY MUNEEM login is: $otp. Valid for 10 minutes.";
            $this->sendSmsMsg91($request->phone, $msg, env('DLT_CODE'));

            Log::info('OTP sent for user login', ['phone' => $request->phone]);

            return response()->json([
                'status' => true,
                'message' => 'OTP sent for login',
                'data' => ['phone' => $request->phone],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in login_process', [
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
     * Doctor/Vendor Login OTP Verify
     */
    public function login_otp_verify(Request $request)
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
            $otpRecord = DB::table('tbl_otp')
                ->where('phone', $request->phone)
                ->where('otp', $request->otp)
                ->where('expires_at', '>', now())
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired OTP',
                ], 400);
            }

            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            Auth::login($user);

            DB::table('tbl_otp')->where('id', $otpRecord->id)->delete();

            Log::info('User login verified', ['phone' => $request->phone, 'user_id' => $user->id]);

            return response()->json([
                'status' => true,
                'message' => 'Login verified successfully',
                'user_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in login_otp_verify', [
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
     * Update User Profile
     */
    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string',
            'lname' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated',
                ], 401);
            }

            $updated = User::where('id', $user->id)->update([
                'f_name' => $request->fname,
                'l_name' => $request->lname,
            ]);

            if ($updated) {
                Log::info('User profile updated', ['user_id' => $user->id]);
                return response()->json([
                    'status' => true,
                    'message' => 'Profile updated successfully',
                ]);
            } else {
                Log::error('Failed to update user profile', ['user_id' => $user->id]);
                return response()->json([
                    'status' => false,
                    'message' => 'Some unknown error occurred',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in update_profile', [
                'user_id' => Auth::id(),
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
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info('User logged out', ['user_id' => Auth::id()]);

            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully',
            ]);
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
            // Mock SMS sending for localhost testing
            Log::info('Mock SMS sent', [
                'phone' => $phone,
                'message' => $message,
                'dlt' => $dlt,
            ]);
    
            // Extract OTP from message (assuming format: "Your OTP ... is: 123456...")
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

    /**
     * Farmer Register OTP Verification
     */
   
}