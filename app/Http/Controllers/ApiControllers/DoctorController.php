<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class DoctorController extends Controller
{
    public function getRequests(Request $request)
    {
        try {
            // Authenticate doctor using 'doctor' guard
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
}
