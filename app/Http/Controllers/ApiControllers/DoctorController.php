<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
class DoctorController extends Controller
{
    public function getRequests(Request $request)
    {
        try {
            $doctor = auth('doctor')->user();
            Log::info('GetRequests auth attempt', [
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

            // Pagination parameters
            $limit = 20;
            $pageIndex = $request->header('Index', 1); // Fallback to page 1
            $pageIndex = max(1, (int)$pageIndex); // Ensure positive integer

            // Fetch requests with pagination
            $requests = DoctorRequest::where('doctor_id', $doctor->id)
                ->where('is_expert', $doctor->is_expert)
                ->where('payment_status', 1)
                ->orderBy('id', 'desc')
                ->with('farmer') // Eager load farmer
                ->paginate($limit, ['*'], 'page', $pageIndex);

            $count = $requests->total();
            $pages = ceil($count / $limit);

            // Process requests
            $data = $requests->map(function ($request) {
                $status = $request->status == 0 ? 'Pending' : 'Completed';
                $bgColor = $request->status == 0 ? '#e4a11b' : '#139c49';
                $date = (new \DateTime($request->date))->format('d/m/Y');

                return [
                    'id' => $request->id,
                    'farmer_name' => $request->farmer->name ?? '',
                    'farmer_phone' => $request->farmer->phone ?? '',
                    'farmer_village' => $request->farmer->village ?? '',
                    'reason' => $request->reason ?? '',
                    'description' => $request->description ?? '',
                    'is_expert' => $request->is_expert,
                    'fees' => $request->fees,
                    'image1' => $request->image1 ? url('storage/' . $request->image1) : '',
                    'image2' => $request->image2 ? url('storage/' . $request->image2) : '',
                    'image3' => $request->image3 ? url('storage/' . $request->image3) : '',
                    'image4' => $request->image4 ? url('storage/' . $request->image4) : '',
                    'image5' => $request->image5 ? url('storage/' . $request->image5) : '',
                    'status' => $status,
                    'bg_color' => $bgColor,
                    'date' => $date,
                ];
            })->toArray();

            // Custom pagination (mimicking CreatePagination)
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
                'doctor_id' => auth('doctor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching requests: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function reqMarkComplete(Request $request, $id)
    {
        try {
            // Authenticate doctor using 'doctor' guard
            $doctor = auth('doctor')->user();
            Log::info('ReqMarkComplete auth attempt', [
                'doctor_id' => $doctor ? $doctor->id : null,
                'is_active' => $doctor ? ($doctor->is_active ?? 'missing') : null,
                'is_approved' => $doctor ? ($doctor->is_approved ?? 'missing') : null,
                'request_id' => $id,
                'request_token' => $request->bearerToken(),
            ]);

            if (!$doctor || !$doctor->is_active || !$doctor->is_approved) {
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
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
                    'status' => 201,
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error in reqMarkComplete', [
                'doctor_id' => auth('doctor')->id() ?? null,
                'request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error marking request as complete: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function getProfile(Request $request)
    {
        try {
            $doctor = auth('doctor')->user();
            Log::info('GetProfile auth attempt', [
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

            $image = $doctor->image ? url('storage/' . $doctor->image) : '';
            // $state = $doctor->state()->first()->state_name ?? '';

            $data = [
                'name' => $doctor->name,
                'district' => $doctor->district,
                'city' => $doctor->city,
                // 'state' => $state,
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
                'doctor_id' => auth('doctor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error fetching profile: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $doctor = auth('doctor')->user();
            Log::info('UpdateProfile auth attempt', [
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
                    'status' => 201,
                ], 422);
            }

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

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = 'image_' . now()->format('YmdHis') . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('uploads/doctor', $filename, 'public');
                $data['image'] = $path;
            } else {
                $data['image'] = $doctor->image;
            }

            $doctor->update($data);

            return response()->json([
                'message' => 'Success',
                'status' => 200,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in updateProfile', [
                'doctor_id' => auth('doctor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Error updating profile: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }
}
