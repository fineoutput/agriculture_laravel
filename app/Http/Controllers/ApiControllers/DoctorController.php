<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorTank;
use App\Models\DoctorSemenTransaction;
use App\Models\PaymentsReq;
use App\Models\DoctorCanister;
use App\Models\DoctorNotification;
use App\Models\PaymentTransaction;
use App\Models\DoctorRequest;
use App\Models\DoctorSlider;
use App\Models\ExpertiseCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class DoctorController extends Controller
{
   public function getRequests(Request $request)
{
    try {
        // Token-based authentication
        $token = $request->header('Authentication');
        if (!$token) {
            return response()->json([
                'message' => 'Token required!',
                'status' => 401,
            ], 401);
        }

        $doctor = Doctor::where('auth', $token)->first();

        Log::info('GetRequests auth attempt', [
            'doctor_id' => $doctor ? $doctor->id : null,
            'is_active' => $doctor ? ($doctor->is_active ?? 'missing') : null,
            'is_approved' => $doctor ? ($doctor->is_approved ?? 'missing') : null,
            'request_token' => $token,
        ]);

        if (!$doctor || !$doctor->is_active || !$doctor->is_approved) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        // Pagination parameters
        $limit = 20;
        $pageIndex = $request->header('Index', 1); // Default to page 1
        $pageIndex = max(1, (int)$pageIndex);

        // Fetch requests with pagination
        $requests = DoctorRequest::where('doctor_id', $doctor->id)
            ->where('is_expert', $doctor->is_expert)
            ->where('payment_status', 1)
            ->orderBy('id', 'desc')
            ->with('farmer')
            ->paginate($limit, ['*'], 'page', $pageIndex);

        $count = $requests->total();
        $pages = ceil($count / $limit);

        // Format request data
        $data = $requests->map(function ($req) {
            $status = $req->status == 0 ? 'Pending' : 'Completed';
            $bgColor = $req->status == 0 ? '#e4a11b' : '#139c49';
            $date = (new \DateTime($req->date))->format('d/m/Y');

            return [
                'id' => $req->id,
                'farmer_name' => $req->farmer->name ?? '',
                'farmer_phone' => $req->farmer->phone ?? '',
                'farmer_village' => $req->farmer->village ?? '',
                'reason' => $req->reason ?? '',
                'description' => $req->description ?? '',
                'is_expert' => $req->is_expert,
                'fees' => $req->fees,
                'image1' => $req->image1 ? url('storage/' . $req->image1) : '',
                'image2' => $req->image2 ? url('storage/' . $req->image2) : '',
                'image3' => $req->image3 ? url('storage/' . $req->image3) : '',
                'image4' => $req->image4 ? url('storage/' . $req->image4) : '',
                'image5' => $req->image5 ? url('storage/' . $req->image5) : '',
                'status' => $status,
                'bg_color' => $bgColor,
                'date' => $date,
            ];
        })->toArray();

        $pagination = [
            'current_page' => $requests->currentPage(),
            'last_page' => $requests->lastPage(),
            'per_page' => $limit,
            'total' => $count,
            'next_page_url' => $requests->nextPageUrl(),
            'prev_page_url' => $requests->previousPageUrl(),
        ];

        if ($data) {
            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'pagination' => $pagination,
                'last' => $pages,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No Orders Found!',
                'status' => 201,
                'data' => [],
            ], 200);
        }

    } catch (\Exception $e) {
        Log::error('Error in getRequests', [
            'doctor_id' => $doctor->id ?? null,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error fetching requests: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}


   public function reqMarkComplete(Request $request, $id)
{
    try {
        // Token-based authentication
        $token = $request->header('Authentication');
        if (!$token) {
            return response()->json([
                'message' => 'Token required!',
                'status' => 401,
            ], 401);
        }

        // Fetch doctor using the token
        $doctor = Doctor::where('auth', $token)->first();

        Log::info('ReqMarkComplete auth attempt', [
            'doctor_id' => $doctor ? $doctor->id : null,
            'is_active' => $doctor ? ($doctor->is_active ?? 'missing') : null,
            'is_approved' => $doctor ? ($doctor->is_approved ?? 'missing') : null,
            'request_id' => $id,
            'request_token' => $token,
        ]);

        if (!$doctor || !$doctor->is_active || !$doctor->is_approved) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        // Update request status
        $updated = DoctorRequest::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->where('is_expert', $doctor->is_expert)
            ->update(['status' => 1]);

        if ($updated) {
            return response()->json([
                'message' => 'Success!',
                'status' => 200,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Some error occurred!',
                'status' => 400,
            ], 400);
        }

    } catch (\Exception $e) {
        Log::error('Error in reqMarkComplete', [
            'doctor_id' => $doctor->id ?? null,
            'request_id' => $id,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error marking request as complete: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}


  public function getProfile(Request $request)
{
    try {
        // Token-based authentication
        $token = $request->header('Authentication');
        if (!$token) {
            return response()->json([
                'message' => 'Token required!',
                'status' => 401,
            ], 401);
        }

        // Fetch doctor by token
        $doctor = Doctor::where('auth', $token)->first();

        Log::info('GetProfile auth attempt', [
            'doctor_id' => $doctor ? $doctor->id : null,
            'is_active' => $doctor ? ($doctor->is_active ?? 'missing') : null,
            'is_approved' => $doctor ? ($doctor->is_approved ?? 'missing') : null,
            'request_token' => $token,
        ]);

        // Check permissions
        if (!$doctor || !$doctor->is_active || !$doctor->is_approved) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        // Prepare doctor profile data
        $image = $doctor->image ? url('storage/' . $doctor->image) : '';

        $data = [
            'name' => $doctor->name,
            'district' => $doctor->district,
            'city' => $doctor->city,
            'state_id' => $doctor->state,
            'phone' => $doctor->phone,
            'email' => $doctor->email,
            'type' => $doctor->type,
            'pincode' => $doctor->pincode,
            'degree' => $doctor->degree,
            'experience' => $doctor->experience,
            'qualification' => $doctor->qualification,
            'commission' => $doctor->commission,
            'aadhar_no' => $doctor->aadhar_no,
            'image' => $image,
            'is_expert' => $doctor->is_expert,
            'expertise' => $doctor->expertise,
            'bank_name' => $doctor->bank_name,
            'bank_phone' => $doctor->bank_phone,
            'bank_ac' => $doctor->bank_ac,
            'ifsc' => $doctor->ifsc,
            'upi' => $doctor->upi,
        ];

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $data,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in getProfile', [
            'doctor_id' => $doctor->id ?? null,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error fetching profile: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}


   public function updateProfile(Request $request){
    try {
        // Token-based authentication
        $token = $request->header('Authentication');
        if (!$token) {
            return response()->json([
                'message' => 'Token required!',
                'status' => 401,
            ], 401);
        }

        // Get doctor from token
        $doctor = Doctor::where('auth', $token)->first();

        Log::info('UpdateProfile auth attempt', [
            'doctor_id' => $doctor ? $doctor->id : null,
            'is_active' => $doctor->is_active ?? 'missing',
            'is_approved' => $doctor->is_approved ?? 'missing',
            'request_token' => $token,
        ]);

        // Check permission
        if (!$doctor || !$doctor->is_active || !$doctor->is_approved) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'district' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'doc_type' => 'required|in:1,2,3,123',
            'qualification' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'experience' => 'nullable|string|max:255',
            'aadhar_no' => 'nullable|string|max:12',
            'expertise' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 422,
            ], 422);
        }

        // Get updatable fields
        $data = $request->only([
            'name',
            'email',
            'district',
            'city',
            'state',
            'qualification',
            'experience',
            'pincode',
            'aadhar_no',
            'expertise',
        ]);

        // Map doc_type to type
        $doc_type = $request->input('doc_type');
        switch ($doc_type) {
            case '1':
                $data['type'] = 'Vet';
                break;
            case '2':
                $data['type'] = 'Livestock Assistant';
                break;
            default:
                $data['type'] = 'Private Practitioner';
                break;
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'image_' . now()->format('YmdHis') . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('uploads/doctor', $filename, 'public');
            $data['image'] = $path;
        } else {
            $data['image'] = $doctor->image;
        }

        // Update doctor profile
        $doctor->update($data);

        return response()->json([
            'message' => 'Success',
            'status' => 200,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in updateProfile', [
            'doctor_id' => $doctor->id ?? null,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error updating profile: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}

public function homeData(Request $request)
{
    try {
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token required!',
                'status' => 401
            ], 401);
        }

        // Authenticate doctor using token
        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403
            ], 403);
        }

        $doctorId = $doctor->id;
        $today = Carbon::today()->toDateString();

        // Today's request count
        $todayReq = DoctorRequest::where('doctor_id', $doctorId)
            ->whereDate('date', $today)
            ->count();

        // Total requests
        $totalReq = DoctorRequest::where('doctor_id', $doctorId)->count();

        // Today's income
        $todayIncome = PaymentTransaction::where('doctor_id', $doctorId)
            ->whereNotNull('req_id')
            ->whereDate('date', $today)
            ->sum('cr');

        // Total income
        $totalIncome = PaymentTransaction::where('doctor_id', $doctorId)
            ->whereNotNull('req_id')
            ->sum('cr');

        // Doctor sliders
        $doctorSlider = DoctorSlider::where('is_active', 1)
            ->get()
            ->map(function ($slider) {
                return [
                    'image' => $slider->image ? url($slider->image) : ''
                ];
            });

        // Notifications
        $notifications = DoctorNotification::where('doctor_id', $doctorId)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'name' => $notification->name,
                    'image' => $notification->image ? url($notification->image) : '',
                    'description' => $notification->dsc,
                    'date' => Carbon::parse($notification->date)->format('d-m-y, g:i a')
                ];
            });

        $notificationCount = DoctorNotification::where('doctor_id', $doctorId)->count();

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => [
                'today_req' => $todayReq,
                'total_req' => $totalReq,
                'today_income' => round($todayIncome, 2),
                'total_income' => round($totalIncome, 2),
                'is_expert' => $doctor->is_expert,
                'doctor_slider' => $doctorSlider,
                'notification_data' => $notifications,
                'notification_count' => $notificationCount
            ]
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in homeData', [
            'error' => $e->getMessage()
        ]);
        return response()->json([
            'message' => 'Something went wrong: ' . $e->getMessage(),
            'status' => 500
        ], 500);
    }
}

    public function updateBankInfo(Request $request)
    {
        try {
            if (!$request->hasAny(['bank_name', 'bank_phone', 'bank_ac', 'ifsc', 'upi'])) {
                return response()->json([
                    'message' => 'Please Insert Data',
                    'status' => 201,
                ], 422);
            }

            $doctor = auth('doctor')->user();
            Log::info('UpdateBankInfo auth attempt', [
                'doctor_id' => $doctor ? $doctor->id : null,
                'is_active' => $doctor ? ($doctor->is_active ?? 'missing') : null,
                'is_approved' => $doctor ? ($doctor->is_approved ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
            ]);

            if (!$doctor || !$doctor->is_active || !$doctor->is_approved) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'bank_name' => 'required|string|max:255',
                'bank_phone' => 'required|string|max:15',
                'bank_ac' => 'required|string|max:50',
                'ifsc' => 'required|string|max:11',
                'upi' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $data = $request->only([
                'bank_name',
                'bank_phone',
                'bank_ac',
                'ifsc',
                'upi',
            ]);

        

                $updated = $doctor->update($data);
            

            if ($updated) { 
                return response()->json([
                    'message' => 'Success',
                    'status' => 200,
                    'data' => [$data],
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error in updateBankInfo', [
                'doctor_id' => auth('doctor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error updating bank info: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function updateLocation(Request $request)
{
    try {
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token missing!',
                'status' => 401,
            ], 401);
        }

        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        Log::info('UpdateLocation auth attempt', [
            'doctor_id' => $doctor->id ?? null,
            'request_token' => $token,
        ]);

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'fcm_token' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 422,
            ], 422);
        }

        $data = $request->only(['latitude', 'longitude', 'fcm_token']);
        $data['updated_at'] = now();

        $doctor->update($data);

        return response()->json([
            'message' => 'Success',
            'status' => 200,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error in updateLocation', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error updating location: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}


    public function paymentInfo(Request $request)
{
    try {
        // Get token from header
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token missing!',
                'status' => 401,
            ], 401);
        }

        // Authenticate doctor using the token
        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        Log::info('PaymentInfo auth attempt', [
            'doctor_id' => $doctor->id ?? null,
            'is_active' => $doctor->is_active ?? null,
            'is_approved' => $doctor->is_approved ?? null,
            'request_token' => $token,
        ]);

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        // Fetch last 20 transactions with req_id
        $transactions = PaymentTransaction::where('doctor_id', $doctor->id)
            ->whereNotNull('req_id')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        $data = $transactions->map(function ($txn) {
            return [
                'req_id' => $txn->req_id,
                'cr' => $txn->cr,
                'date' => (new \DateTime($txn->date))->format('d/m/Y'),
            ];
        })->toArray();

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $data,
            'account' => $doctor->account,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error in paymentInfo', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error fetching payment info: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}


    public function adminPaymentInfo(Request $request)
    {
        try {
            // /** @var \App\Models\Doctor $doctor */
            $doctor = auth('doctor')->user();
            Log::info('AdminPaymentInfo auth attempt', [
                'doctor_id' => $doctor ? $doctor->id : null,
                'is_active' => $doctor ? ($doctor->is_active ?? 'missing') : null,
                'is_approved' => $doctor ? ($doctor->is_approved ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
            ]);

            if (!$doctor || !$doctor->is_active || !$doctor->is_approved) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $paymentRequests = PaymentsReq::where('doctor_id', $doctor->id)
                ->orderBy('id', 'desc')
                ->take(20)
                ->get();

            $data = $paymentRequests->map(function ($req) {
                switch ($req->status) {
                    case 0:
                        $status = 'Pending';
                        break;
                    case 1:
                        $status = 'Completed';
                        break;
                    case 2:
                        $status = 'Rejected';
                        break;
                    default:
                        $status = 'Unknown';
                }
                

                switch ($req->status) {
                    case 0:
                        $bgColor = '#65bcd7';
                        break;
                    case 1:
                        $bgColor = '#139c49';
                        break;
                    case 2:
                        $bgColor = '#dc4c64';
                        break;
                    default:
                        $bgColor = '#000000';
                }
                

                return [
                    'req_id' => $req->id,
                    'amount' => $req->amount,
                    'status' => $status,
                    'bg_color' => $bgColor,
                    'date' => (new \DateTime($req->date))->format('d/m/Y'),
                ];
            })->toArray();

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in adminPaymentInfo', [
                'doctor_id' => auth('doctor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching admin payment info: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function semenTanks(Request $request)
{
    try {
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token missing!',
                'status' => 401,
            ], 401);
        }

        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        Log::info('SemenTanks auth attempt', [
            'doctor_id' => $doctor->id,
            'is_active' => $doctor->is_active,
            'is_approved' => $doctor->is_approved,
            'request_token' => $token,
        ]);

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        $tanks = DoctorTank::where('doctor_id', $doctor->id)
            ->with('canisters')
            ->get();

        $data = $tanks->map(function ($tank, $index) {
            return [
                's_no' => $index + 1,
                'name' => $tank->name,
                'tank_id' => $tank->id,
                'canister' => $tank->canisters->toArray(),
            ];
        })->toArray();

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $data,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error in semenTanks', [
            'doctor_id' => $doctor->id ?? null,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error fetching semen tanks: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}

public function deleteSemenTank(Request $request)
{
    try {
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token missing!',
                'status' => 401,
            ], 401);
        }

        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tbl_doctor_tank,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 422,
            ], 422);
        }

        $id = $request->input('id');

        $tank = DoctorTank::where('doctor_id', $doctor->id)
            ->where('id', $id)
            ->first();

        if (!$tank) {
            return response()->json([
                'message' => 'Tank not found!',
                'status' => 404,
            ], 404);
        }

        DoctorCanister::where('doctor_id', $doctor->id)
            ->where('tank_id', $id)
            ->delete();

        $tank->delete();

        return response()->json([
            'message' => 'Tank Successfully Deleted!',
            'status' => 200,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error deleting semen tank: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}



   public function addSemenTank(Request $request)
{
    try {
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token missing!',
                'status' => 401,
            ], 401);
        }

        // Find doctor from token
        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        Log::info('AddSemenTank auth attempt', [
            'doctor_id' => $doctor->id,
            'is_active' => $doctor->is_active,
            'is_approved' => $doctor->is_approved,
            'name' => $request->input('name'),
            'request_token' => $token,
            'ip_address' => $request->ip(),
        ]);

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 422,
            ], 422);
        }

        $name = $request->input('name');

        $existingTank = DoctorTank::where('doctor_id', $doctor->id)
            ->where('name', $name)
            ->first();

        if ($existingTank) {
            return response()->json([
                'message' => 'Tank name already exists!',
                'status' => 409,
            ], 409);
        }

        $tank = DoctorTank::create([
            'doctor_id' => $doctor->id,
            'name' => $name,
            'date' => now(),
        ]);

        for ($i = 0; $i < 6; $i++) {
            DoctorCanister::create([
                'doctor_id' => $doctor->id,
                'tank_id' => $tank->id,
                'date' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Record Successfully Inserted!',
            'status' => 200,
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error in addSemenTank', [
            'doctor_id' => $doctor->id ?? null,
            'name' => $request->input('name'),
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error adding semen tank: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}

public function updateCanister(Request $request)
{
    try {
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token missing!',
                'status' => 401,
            ], 401);
        }

        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        // ✅ Fetch canister_id BEFORE using it
        $canister_id = $request->input('canister_id');

        Log::info('UpdateCanister auth attempt', [
            'doctor_id' => $doctor->id,
            'canister_id' => $canister_id,
            'request_token' => $token,
            'ip_address' => $request->ip(),
        ]);

        // Validation
        $validator = Validator::make(array_merge($request->all(), ['canister_id' => $canister_id]), [
            'canister_id' => 'required|exists:tbl_doctor_canister,id',
            'bull_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'no_of_units' => 'nullable|numeric|min:0',
            'milk_production_of_mother' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 422,
            ], 422);
        }

        $canister = DoctorCanister::where('id', $canister_id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$canister) {
            return response()->json([
                'message' => 'Canister not found!',
                'status' => 404,
            ], 404);
        }

        $canister->update(array_filter([
            'bull_name' => $request->input('bull_name'),
            'company_name' => $request->input('company_name'),
            'no_of_units' => $request->input('no_of_units'),
            'milk_production_of_mother' => $request->input('milk_production_of_mother'),
            'date' => now(),
        ], fn($v) => !is_null($v)));

        return response()->json([
            'message' => 'Record Successfully Updated!',
            'status' => 200,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in updateCanister', [
            'doctor_id' => $doctor->id ?? null,
            'canister_id' => $request->input('canister_id'),
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error updating canister: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}



   public function sellSemen(Request $request)
{
    try {
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token missing!',
                'status' => 401,
            ], 401);
        }

        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        Log::info('SellSemen auth attempt', [
            'doctor_id' => $doctor->id ?? null,
            'tank_id' => $request->input('tank_id'),
            'canister' => $request->input('canister'),
            'quantity' => $request->input('quantity'),
            'request_token' => $token,
            'ip_address' => $request->ip(),
        ]);

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'tank_id' => 'required|numeric',
            'canister' => 'required|numeric',
            'quantity' => 'required|numeric|min:1',
            'farmer_name' => 'required|string|max:255',
            'farmer_phone' => 'required|string|max:15',
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => 422,
            ], 422);
        }
$tank_id = $request->input('tank_id');
$canister_id = $request->input('canister'); // This is index-based (1-based)
$quantity = $request->input('quantity');

$canisterList = DoctorCanister::where('doctor_id', $doctor->id)
    ->where('tank_id', $tank_id)
    ->get()
    ->values();

$index = $canister_id - 1;

if (!isset($canisterList[$index])) {
    return response()->json([
        'message' => 'Canister not found at index: ' . $canister_id,
        'status' => 404,
    ], 404);
}

$canister = $canisterList[$index];

$available_units = $canister->no_of_units ?? 0;

if ($available_units < $quantity) {
    return response()->json([
        'message' => "Only {$available_units} units available.",
        'status' => 422,
    ], 422);
}

        // Create transaction
        DoctorSemenTransaction::create([
            'doctor_id' => $doctor->id,
            'tank_id' => $tank_id,
            'canister' => $canister_id,
            'bull_name' => $canister->bull_name,
            'company_name' => $canister->company_name,
            'no_of_units' => $canister->no_of_units,
            'sell_unit' => $quantity,
            'milk_production_of_mother' => $canister->milk_production_of_mother,
            'farmer_name' => $request->input('farmer_name'),
            'farmer_phone' => $request->input('farmer_phone'),
            'address' => $request->input('address'),
            'date' => now(),
        ]);

        // Update canister units
        $canister->update([
            'no_of_units' => $available_units - $quantity,
        ]);

        return response()->json([
            'message' => 'Record successfully saved!',
            'status' => 200,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in sellSemen', [
            'doctor_id' => $doctor->id ?? null,
            'tank_id' => $request->input('tank_id'),
            'canister' => $request->input('canister'),
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error recording semen sale: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}


   public function getSemenTransactions(Request $request)
{
    try {
        // Get token from header
        $token = $request->header('Authentication');

        if (!$token) {
            return response()->json([
                'message' => 'Token missing!',
                'status' => 401,
            ], 401);
        }

        // Find doctor using token
        $doctor = Doctor::where('auth', $token)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->first();

        Log::info('GetSemenTransactions auth attempt', [
            'doctor_id' => $doctor->id ?? null,
            'is_active' => $doctor->is_active ?? null,
            'is_approved' => $doctor->is_approved ?? null,
            'request_token' => $token,
            'ip_address' => $request->ip(),
        ]);

        if (!$doctor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 403,
            ], 403);
        }

        // Fetch transactions
        $transactions = DoctorSemenTransaction::where('doctor_id', $doctor->id)
            ->orderBy('id', 'desc')
            ->with('tank')
            ->get();

        // Format result
        $data = $transactions->map(function ($transaction, $index) {
            return [
                's_no' => $index + 1,
                'tank' => $transaction->tank ? $transaction->tank->name : 'Unknown Tank',
                'canister' => 'Canister ' . $transaction->canister,
                'sell_unit' => $transaction->sell_unit,
                'farmer_name' => $transaction->farmer_name,
                'farmer_phone' => $transaction->farmer_phone,
                'address' => $transaction->address,
                'date' => (new \DateTime($transaction->date))->format('d/m/Y'),
            ];
        })->toArray();

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $data,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error in getSemenTransactions', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Error fetching semen transactions: ' . $e->getMessage(),
            'status' => 500,
        ], 500);
    }
}

    
    public function getExpertCategories()
{
    $categories = ExpertiseCategory::where('is_active', 1)->get(['id', 'name']);

    $data = $categories->map(function ($category) {
        return [
            'value' => $category->id,
            'label' => $category->name,
        ];
    });

    return response()->json([
        'message' => 'Success',
        'status' => 200,
        'data' => $data,
    ]);
}
}
