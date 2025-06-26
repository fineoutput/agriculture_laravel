<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Farmer;
use App\Models\State;
use App\Models\Order1;
use App\Models\Order2;
use App\Models\VendorSlider;
use App\Models\VendorSlider2;
use App\Models\VendorNotification;
use App\Models\PaymentTransaction;
use App\Models\PaymentsReq;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
class VendorController extends Controller
{
    public function newOrders(Request $request)
    {
        try {
            $token = $request->header('Authentication');
            if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $vendor = Vendor::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$vendor) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }
            // /** @var \App\Models\Vendor $vendor */
            // $vendor = auth('vendor')->user();
            // Log::info('NewOrders auth attempt', [
            //     'vendor_id' => $vendor ? $vendor->id : null,
            //     'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
            //     'request_token' => $request->bearerToken(),
            //     'ip_address' => $request->ip(),
            // ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('NewOrders: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                    'is_approved' => $vendor ? $vendor->is_approved : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $page_index = (int) $request->header('Index', 1);
            $limit = 20;
            $start = ($page_index - 1) * $limit;

            $count = Order1::where('vendor_id', $vendor->id)
                          ->where('is_admin', 0)
                          ->where('payment_status', 1)
                          ->where('order_status', 1)
                          ->count();

            $orders = Order2::where('vendor_id', $vendor->id)
                           ->where('is_admin', 0)
                           ->whereIn('payment_status', [1, 2])
                           ->where('order_status', 1)
                           ->orderBy('id', 'desc')
                           ->offset($start)
                           ->limit($limit)
                           ->with(['farmer', 'items'])
                           ->get();

            $pages = (int) ceil($count / $limit);
            $pagination = $this->createPagination($page_index, $pages);

            $data = [];

            if ($orders->isNotEmpty()) {
                foreach ($orders as $order) {
                    $status_map = [
                        1 => ['status' => 'Pending', 'bg_color' => '#65bcd7'],
                        2 => ['status' => 'Accepted', 'bg_color' => '#3b71ca'],
                        3 => ['status' => 'Dispatched', 'bg_color' => '#e4a11b'],
                        4 => ['status' => 'Completed', 'bg_color' => '#139c49'],
                        5 => ['status' => 'Rejected', 'bg_color' => '#dc4c64'],
                        6 => ['status' => 'Cancelled', 'bg_color' => '#dc4c64'],
                    ];

                    $status_info = $status_map[$order->order_status] ?? ['status' => 'Unknown', 'bg_color' => '#000000'];

                    $details = $order->items->map(function ($item) {
                        $image = !empty($item->image) ? url($item->image) : '';
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name_en ?: '',
                            'image' => $image,
                            'qty' => $item->qty,
                            'selling_price' => $item->selling_price,
                            'total_amount' => $item->total_amount,
                        ];
                    })->toArray();

                    $data[] = [
                        'id' => $order->id,
                        'farmer_name' => $order->name ?? '',
                        'farmer_phone' => $order->phone ?? '',
                        'farmer_address' => $order->address,
                        'farmer_city' => $order->city,
                        'farmer_state' => $order->state,
                        'farmer_district' => $order->district,
                        'farmer_pincode' => $order->pincode,
                        'charges' => $order->charges,
                        'total_amount' => $order->total_amount,
                        'final_amount' => $order->final_amount,
                        'pro_count' => $order->items->count(),
                        'date' => $order->date ? date('d/m/Y', strtotime($order->date)) : '',
                        'status' => $status_info['status'],
                        'bg_color' => $status_info['bg_color'],
                        'details' => $details,
                    ];
                }

                Log::info('NewOrders: Query results', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                    'orders_count' => count($data),
                    'total_pages' => $pages,
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $data,
                    'pagination' => $pagination,
                    'last' => $pages,
                ], 200);
            } else {
                Log::info('NewOrders: No orders found', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                ]);

                return response()->json([
                    'message' => 'No Orders Found!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error in newOrders', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving orders: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }


    public function acceptedOrders(Request $request)
    {
        try {
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('AcceptedOrders auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('AcceptedOrders: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                    'is_approved' => $vendor ? $vendor->is_approved : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $page_index = (int) $request->header('Index', 1);
            $limit = 20;
            $start = ($page_index - 1) * $limit;

            $count = Order1::where('vendor_id', $vendor->id)
                          ->where('is_admin', 0)
                          ->where('payment_status', 1)
                          ->where('order_status', 2)
                          ->count();

            $orders = Order2::where('vendor_id', $vendor->id)
                           ->where('is_admin', 0)
                           ->whereIn('payment_status', [1, 2])
                           ->where('order_status', 2)
                           ->orderBy('id', 'desc')
                           ->offset($start)
                           ->limit($limit)
                           ->with(['farmer', 'items'])
                           ->get();

            $pages = (int) ceil($count / $limit);
            $pagination = $this->createPagination($page_index, $pages);

            $data = [];

            if ($orders->isNotEmpty()) {
                foreach ($orders as $order) {
                    $status_map = [
                        1 => ['status' => 'Pending', 'bg_color' => '#65bcd7'],
                        2 => ['status' => 'Accepted', 'bg_color' => '#3b71ca'],
                        3 => ['status' => 'Dispatched', 'bg_color' => '#e4a11b'],
                        4 => ['status' => 'Completed', 'bg_color' => '#139c49'],
                        5 => ['status' => 'Rejected', 'bg_color' => '#dc4c64'],
                        6 => ['status' => 'Cancelled', 'bg_color' => '#dc4c64'],
                    ];

                    $status_info = $status_map[$order->order_status] ?? ['status' => 'Unknown', 'bg_color' => '#000000'];

                    $details = $order->items->map(function ($item) {
                        $image = !empty($item->image) ? url($item->image) : '';
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name_en ?: '',
                            'image' => $image,
                            'qty' => $item->qty,
                            'selling_price' => $item->selling_price,
                            'total_amount' => $item->total_amount,
                        ];
                    })->toArray();

                    $data[] = [
                        'id' => $order->id,
                        'farmer_name' => $order->farmer->name ?? '',
                        'farmer_phone' => $order->farmer->phone ?? '',
                        'charges' => $order->charges,
                        'total_amount' => $order->total_amount,
                        'final_amount' => $order->final_amount,
                        'pro_count' => $order->items->count(),
                        'date' => $order->date ? date('d/m/Y', strtotime($order->date)) : '',
                        'status' => $status_info['status'],
                        'bg_color' => $status_info['bg_color'],
                        'details' => $details,
                    ];
                }

                Log::info('AcceptedOrders: Query results', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                    'orders_count' => count($data),
                    'total_pages' => $pages,
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $data,
                    'pagination' => $pagination,
                    'last' => $pages,
                ], 200);
            } else {
                Log::info('AcceptedOrders: No orders found', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                ]);

                return response()->json([
                    'message' => 'No Orders Found!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error in acceptedOrders', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving orders: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function dispatchedOrders(Request $request)
    {
        try {
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('DispatchedOrders auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('DispatchedOrders: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                    'is_approved' => $vendor ? $vendor->is_approved : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $page_index = (int) $request->header('Index', 1);
            $limit = 20;
            $start = ($page_index - 1) * $limit;

            $count = Order1::where('vendor_id', $vendor->id)
                          ->where('is_admin', 0)
                          ->where('payment_status', 1)
                          ->where('order_status', 3)
                          ->count();

            $orders = Order2::where('vendor_id', $vendor->id)
                           ->where('is_admin', 0)
                           ->whereIn('payment_status', [1, 2])
                           ->where('order_status', 3)
                           ->orderBy('id', 'desc')
                           ->offset($start)
                           ->limit($limit)
                           ->with(['farmer', 'items'])
                           ->get();

            $pages = (int) ceil($count / $limit);
            $pagination = $this->createPagination($page_index, $pages);

            $data = [];

            if ($orders->isNotEmpty()) {
                foreach ($orders as $order) {
                    $status_map = [
                        1 => ['status' => 'Pending', 'bg_color' => '#65bcd7'],
                        2 => ['status' => 'Accepted', 'bg_color' => '#3b71ca'],
                        3 => ['status' => 'Dispatched', 'bg_color' => '#e4a11b'],
                        4 => ['status' => 'Completed', 'bg_color' => '#139c49'],
                        5 => ['status' => 'Rejected', 'bg_color' => '#dc4c64'],
                        6 => ['status' => 'Cancelled', 'bg_color' => '#dc4c64'],
                    ];

                    $status_info = $status_map[$order->order_status] ?? ['status' => 'Unknown', 'bg_color' => '#000000'];

                    $details = $order->items->map(function ($item) {
                        $image = !empty($item->image) ? url($item->image) : '';
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name_en ?: '',
                            'image' => $image,
                            'qty' => $item->qty,
                            'selling_price' => $item->selling_price,
                            'total_amount' => $item->total_amount,
                        ];
                    })->toArray();

                    $data[] = [
                        'id' => $order->id,
                        'farmer_name' => $order->farmer->name ?? '',
                        'farmer_phone' => $order->farmer->phone ?? '',
                        'charges' => $order->charges,
                        'total_amount' => $order->total_amount,
                        'final_amount' => $order->final_amount,
                        'pro_count' => $order->items->count(),
                        'date' => $order->date ? date('d/m/Y', strtotime($order->date)) : '',
                        'status' => $status_info['status'],
                        'bg_color' => $status_info['bg_color'],
                        'details' => $details,
                    ];
                }

                Log::info('DispatchedOrders: Query results', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                    'orders_count' => count($data),
                    'total_pages' => $pages,
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $data,
                    'pagination' => $pagination,
                    'last' => $pages,
                ], 200);
            } else {
                Log::info('DispatchedOrders: No orders found', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                ]);

                return response()->json([
                    'message' => 'No Orders Found!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error in dispatchedOrders', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving orders: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }


    public function completedOrders(Request $request)
    {
        try {
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('CompletedOrders auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('CompletedOrders: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                    'is_approved' => $vendor ? $vendor->is_approved : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $page_index = (int) $request->header('Index', 1);
            $limit = 20;
            $start = ($page_index - 1) * $limit;

            $count = Order1::where('vendor_id', $vendor->id)
                          ->where('is_admin', 0)
                          ->where('payment_status', 1)
                          ->where('order_status', 4)
                          ->count();

            $orders = Order2::where('vendor_id', $vendor->id)
                           ->where('is_admin', 0)
                           ->whereIn('payment_status', [1, 2])
                           ->where('order_status', 4)
                           ->orderBy('id', 'desc')
                           ->offset($start)
                           ->limit($limit)
                           ->with(['farmer', 'items'])
                           ->get();

            $pages = (int) ceil($count / $limit);
            $pagination = $this->createPagination($page_index, $pages);

            $data = [];

            if ($orders->isNotEmpty()) {
                foreach ($orders as $order) {
                    $status_map = [
                        1 => ['status' => 'Pending', 'bg_color' => '#65bcd7'],
                        2 => ['status' => 'Accepted', 'bg_color' => '#3b71ca'],
                        3 => ['status' => 'Dispatched', 'bg_color' => '#e4a11b'],
                        4 => ['status' => 'Completed', 'bg_color' => '#139c49'],
                        5 => ['status' => 'Rejected', 'bg_color' => '#dc4c64'],
                        6 => ['status' => 'Cancelled', 'bg_color' => '#dc4c64'],
                    ];

                    $status_info = $status_map[$order->order_status] ?? ['status' => 'Unknown', 'bg_color' => '#000000'];

                    $details = $order->items->map(function ($item) {
                        $image = !empty($item->image) ? url($item->image) : '';
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name_en ?: '',
                            'image' => $image,
                            'qty' => $item->qty,
                            'selling_price' => $item->selling_price,
                            'total_amount' => $item->total_amount,
                        ];
                    })->toArray();

                    $data[] = [
                        'id' => $order->id,
                        'farmer_name' => $order->farmer->name ?? '',
                        'farmer_phone' => $order->farmer->phone ?? '',
                        'charges' => $order->charges,
                        'total_amount' => $order->total_amount,
                        'final_amount' => $order->final_amount,
                        'pro_count' => $order->items->count(),
                        'date' => $order->date ? date('d/m/Y', strtotime($order->date)) : '',
                        'status' => $status_info['status'],
                        'bg_color' => $status_info['bg_color'],
                        'details' => $details,
                    ];
                }

                Log::info('CompletedOrders: Query results', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                    'orders_count' => count($data),
                    'total_pages' => $pages,
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $data,
                    'pagination' => $pagination,
                    'last' => $pages,
                ], 200);
            } else {
                Log::info('CompletedOrders: No orders found', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                ]);

                return response()->json([
                    'message' => 'No Orders Found!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error in completedOrders', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving orders: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function cancelledOrders(Request $request)
    {
        try {
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('CancelledOrders auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('CancelledOrders: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                    'is_approved' => $vendor ? $vendor->is_approved : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $page_index = (int) $request->header('Index', 1);
            $limit = 20;
            $start = ($page_index - 1) * $limit;

            $count = Order1::where('vendor_id', $vendor->id)
                          ->where('is_admin', 0)
                          ->where('payment_status', 1)
                          ->where('order_status', 6)
                          ->count();

            $orders = Order2::where('vendor_id', $vendor->id)
                           ->where('is_admin', 0)
                           ->whereIn('payment_status', [1, 2])
                           ->where('order_status', 6)
                           ->orderBy('id', 'desc')
                           ->offset($start)
                           ->limit($limit)
                           ->with(['farmer', 'items'])
                           ->get();

            $pages = (int) ceil($count / $limit);
            $pagination = $this->createPagination($page_index, $pages);

            $data = [];

            if ($orders->isNotEmpty()) {
                foreach ($orders as $order) {
                    $status_map = [
                        1 => ['status' => 'Pending', 'bg_color' => '#65bcd7'],
                        2 => ['status' => 'Accepted', 'bg_color' => '#3b71ca'],
                        3 => ['status' => 'Dispatched', 'bg_color' => '#e4a11b'],
                        4 => ['status' => 'Completed', 'bg_color' => '#139c49'],
                        5 => ['status' => 'Rejected', 'bg_color' => '#dc4c64'],
                        6 => ['status' => 'Cancelled', 'bg_color' => '#dc4c64'],
                    ];

                    $status_info = $status_map[$order->order_status] ?? ['status' => 'Unknown', 'bg_color' => '#000000'];

                    $details = $order->items->map(function ($item) {
                        $image = !empty($item->image) ? url($item->image) : '';
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name_en ?: '',
                            'image' => $image,
                            'qty' => $item->qty,
                            'selling_price' => $item->selling_price,
                            'total_amount' => $item->total_amount,
                        ];
                    })->toArray();

                    $data[] = [
                        'id' => $order->id,
                        'farmer_name' => $order->farmer->name ?? '',
                        'farmer_phone' => $order->farmer->phone ?? '',
                        'charges' => $order->charges,
                        'total_amount' => $order->total_amount,
                        'final_amount' => $order->final_amount,
                        'pro_count' => $order->items->count(),
                        'date' => $order->date ? date('d/m/Y', strtotime($order->date)) : '',
                        'status' => $status_info['status'],
                        'bg_color' => $status_info['bg_color'],
                        'details' => $details,
                    ];
                }

                Log::info('CancelledOrders: Query results', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                    'orders_count' => count($data),
                    'total_pages' => $pages,
                ]);

                return response()->json([
                    'message' => 'Success!',
                    'status' => 200,
                    'data' => $data,
                    'pagination' => $pagination,
                    'last' => $pages,
                ], 200);
            } else {
                Log::info('CancelledOrders: No orders found', [
                    'vendor_id' => $vendor->id,
                    'page_index' => $page_index,
                ]);

                return response()->json([
                    'message' => 'No Orders Found!',
                    'status' => 201,
                    'data' => [],
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Error in cancelledOrders', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving orders: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function updateOrderStatus(Request $request)
    {
        try {
            // /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('UpdateOrderStatus auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('UpdateOrderStatus: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                    'is_approved' => $vendor ? $vendor->is_approved : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:tbl_order1,id',
                'status' => 'required|string|in:accept,dispatch,complete,reject',
            ]);

            if ($validator->fails()) {
                Log::warning('UpdateOrderStatus: Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            $orderId = $request->input('id');
            $status = $request->input('status');

            $order = Order1::where('id', $orderId)
                          ->where('vendor_id', $vendor->id)
                          ->where('is_admin', 0)
                          ->first();

            if (!$order) {
                Log::warning('UpdateOrderStatus: Order not found or not authorized', [
                    'vendor_id' => $vendor->id,
                    'order_id' => $orderId,
                ]);
                return response()->json([
                    'message' => 'Order not found or not authorized!',
                    'status' => 201,
                ], 404);
            }

            // Define valid status transitions
            $statusMap = [
                'accept' => 2, // Accepted
                'dispatch' => 3, // Dispatched
                'complete' => 4, // Completed
                'reject' => 5, // Rejected
            ];

            $allowedTransitions = [
                1 => ['accept', 'reject'], // From Pending
                2 => ['dispatch', 'reject'], // From Accepted
                3 => ['complete', 'reject'], // From Dispatched
                4 => [], // From Completed (no transitions)
                5 => [], // From Rejected (no transitions)
                6 => [], // From Cancelled (no transitions)
            ];

            $currentStatus = $order->order_status;
            if (!in_array($status, $allowedTransitions[$currentStatus] ?? [])) {
                Log::warning('UpdateOrderStatus: Invalid status transition', [
                    'vendor_id' => $vendor->id,
                    'order_id' => $orderId,
                    'current_status' => $currentStatus,
                    'requested_status' => $status,
                ]);
                return response()->json([
                    'message' => "Invalid status transition from {$currentStatus} to {$status}!",
                    'status' => 201,
                ], 422);
            }

            $newStatus = $statusMap[$status];

            if ($status === 'reject') {
                // Handle rejection with inventory update in a transaction
                $success = DB::transaction(function () use ($order, $vendor, $newStatus) {
                    // Update order status
                    $order->order_status = $newStatus;
                    $order->save();

                    // Update inventory for each order item
                    $orderItems = Order2::where('main_id', $order->id)->get();
                    foreach ($orderItems as $item) {
                        $product = Product::where('id', $item->product_id)
                                         ->where('added_by', $vendor->id)
                                         ->where('is_admin', 0)
                                         ->first();

                        if ($product) {
                            $product->inventory += $item->qty;
                            $product->save();
                        } else {
                            Log::warning('UpdateOrderStatus: Product not found or not authorized for inventory update', [
                                'vendor_id' => $vendor->id,
                                'order_id' => $order->id,
                                'product_id' => $item->product_id,
                            ]);
                            throw new \Exception('Product not found or not authorized for inventory update!');
                        }
                    }

                    return true;
                });

                if (!$success) {
                    Log::error('UpdateOrderStatus: Transaction failed for reject status', [
                        'vendor_id' => $vendor->id,
                        'order_id' => $orderId,
                        'status' => $status,
                    ]);
                    return response()->json([
                        'message' => 'Some error occurred during inventory update!',
                        'status' => 201,
                    ], 500);
                }
            } else {
                // Update order status for non-reject cases
                $order->order_status = $newStatus;
                $success = $order->save();
            }

            if ($success) {
                Log::info('UpdateOrderStatus: Status updated successfully', [
                    'vendor_id' => $vendor->id,
                    'order_id' => $orderId,
                    'new_status' => $status,
                ]);
                return response()->json([
                    'message' => 'Status updated successfully!',
                    'status' => 200,
                ], 200);
            } else {
                Log::error('UpdateOrderStatus: Failed to update order status', [
                    'vendor_id' => $vendor->id,
                    'order_id' => $orderId,
                    'status' => $status,
                ]);
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in updateOrderStatus', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'order_id' => $request->input('id') ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error updating order status: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function paymentInfo(Request $request)
    {
        try {
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('PaymentInfo auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('PaymentInfo: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                    'is_approved' => $vendor ? $vendor->is_approved : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $transactions = PaymentTransaction::where('vendor_id', $vendor->id)
                                             ->whereNotNull('req_id')
                                             ->orderBy('id', 'desc')
                                             ->take(20)
                                             ->get();

            $data = $transactions->map(function ($txn) {
                return [
                    'req_id' => $txn->req_id,
                    'cr' => $txn->cr,
                    'date' => $txn->date ? date('d/m/Y', strtotime($txn->date)) : '',
                ];
            })->toArray();

            Log::info('PaymentInfo: Query results', [
                'vendor_id' => $vendor->id,
                'transactions_count' => count($data),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
                'account' => $vendor->account,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in paymentInfo', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving payment information: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function adminPaymentInfo(Request $request)
    {
        try {
            /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('AdminPaymentInfo auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? ($vendor->is_active ?? 'missing') : null,
                'request_token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
            ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('AdminPaymentInfo: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                    'is_active' => $vendor ? $vendor->is_active : null,
                    'is_approved' => $vendor ? $vendor->is_approved : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            $requests = PaymentsReq::where('vendor_id', $vendor->id)
                                     ->orderBy('id', 'desc')
                                     ->take(20)
                                     ->get();

            $data = $requests->map(function ($req) {
                switch ($req->status) {
                    case 0:
                        $statusInfo = ['status' => 'Pending', 'bg_color' => '#65bcd7'];
                        break;
                    case 1:
                        $statusInfo = ['status' => 'Completed', 'bg_color' => '#139c49'];
                        break;
                    case 2:
                        $statusInfo = ['status' => 'Rejected', 'bg_color' => '#dc4c64'];
                        break;
                    default:
                        $statusInfo = ['status' => 'Unknown', 'bg_color' => '#000000'];
                        break;
                }
                

                return [
                    'req_id' => $req->id,
                    'amount' => $req->amount,
                    'status' => $statusInfo['status'],
                    'bg_color' => $statusInfo['bg_color'],
                    'date' => $req->date ? date('d/m/Y', strtotime($req->date)) : '',
                ];
            })->toArray();

            Log::info('AdminPaymentInfo: Query results', [
                'vendor_id' => $vendor->id,
                'requests_count' => count($data),
            ]);

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in adminPaymentInfo', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Error retrieving payment request information: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }


public function vendorProducts(Request $request)
{
    try {
        $authentication = $request->header('Authentication');
        $page_index = (int) $request->header('Index', 1);
        $search = $request->query('search');
        $limit = 20;
        $start = ($page_index - 1) * $limit;

        // Get vendor by auth token
        $vendor = Vendor::where([
            ['is_active', 1],
            ['is_approved', 1],
            ['auth', $authentication]
        ])->first();

        if (!$vendor) {
            return response()->json([
                'message' => 'Permission Denied!',
                'status' => 201
            ], 403);
        }

        // Count total matching products
        $query = Product::where('added_by', $vendor->id)
                        ->where('is_admin', 0);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name_english', 'like', "%$search%")
                  ->orWhere('name_hindi', 'like', "%$search%")
                  ->orWhere('name_punjabi', 'like', "%$search%");
            });
        }

        $count = $query->count();
        $pages = (int) ceil($count / $limit);

        // Apply pagination
        $products = $query->orderByDesc('id')
                          ->offset($start)
                          ->limit($limit)
                          ->get();

        $data = [];
        foreach ($products as $pro) {
            $image = $pro->image ? url($pro->image) : '';
            $stock = $pro->inventory != 0 ? 'In Stock' : 'Out of Stock';

            $discount = (int) $pro->mrp - (int) $pro->selling_price;
            $percent = $discount > 0 ? round($discount / $pro->mrp * 100) : 0;

            $status = $pro->is_active ? 'Active' : 'Inactive';
            $bg_color = $pro->is_active ? '#139c49' : '#ffc30e';

            $data[] = [
                'pro_id' => $pro->id,
                'name_english' => $pro->name_english,
                'name_hindi' => $pro->name_hindi,
                'name_punjabi' => $pro->name_punjabi,
                'description_english' => $pro->description_english,
                'description_hindi' => $pro->description_hindi,
                'description_punjabi' => $pro->description_punjabi,
                'image' => $image,
                'min_qty' => $pro->min_qty ?: 1,
                'mrp' => $pro->mrp,
                'selling_price' => $pro->selling_price,
                'suffix' => $pro->suffix,
                'stock' => $stock,
                'percent' => $percent,
                'inventory' => $pro->inventory,
                'vendor_id' => $pro->added_by,
                'is_active' => $pro->is_active,
                'status' => $status,
                'bg_color' => $bg_color,
                'is_admin' => $pro->is_admin,
            ];
        }

        return response()->json([
            'message' => 'Success!',
            'status' => 200,
            'data' => $data,
            'pagination' => $this->createPagination($page_index, $pages),
            'last' => $pages
        ]);
    } catch (\Exception $e) {
        Log::error('Error in vendorProducts API', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'message' => 'Internal Server Error',
            'status' => 500
        ], 500);
    }
}
    public function addVendorProduct(Request $request)
    {
        try {
            // Authenticate vendor
            // /** @var \App\Models\Vendor $vendor */
           $token = $request->header('Authentication');
            if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $vendor = Vendor::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$vendor) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'mrp' => 'nullable|numeric|min:0',
                'pro_id' => 'nullable|integer|exists:tbl_products,id',
                'selling_price' => 'required|numeric|min:0',
                'inventory' => 'required|integer|min:0',
                'is_active' => 'required|in:0,1',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
                'name_hindi' => 'nullable|string|max:255',
                'name_punjabi' => 'nullable|string|max:255',
                'name_marathi' => 'nullable|string|max:255',
                'description_hindi' => 'nullable|string|max:1000',
                'description_punjabi' => 'nullable|string|max:1000',
                'description_marathi' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                Log::warning('AddVendorProduct: Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $filename = 'vendor_products_' . date('YmdHis') . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('vendor_products');

                // Create directory if it doesn't exist
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the file to the destination
                $image->move($destinationPath, $filename);
                $imagePath = ['vendor_products/' . $filename]; // Store as array for JSON

                Log::info('AddVendorProduct: Image uploaded', [
                    'vendor_id' => $vendor->id,
                    'image_path' => $imagePath,
                ]);
            }

            $data = [
                'name_english' => $request->input('name'),
                'name_hindi' => $request->input('name_hindi'),
                'name_punjabi' => $request->input('name_punjabi'),
                'name_marathi' => $request->input('name_marathi'),
                'description_english' => $request->input('description'),
                'description_hindi' => $request->input('description_hindi'),
                'description_punjabi' => $request->input('description_punjabi'),
                'description_marathi' => $request->input('description_marathi'),
                'mrp' => $request->input('mrp'),
                'selling_price' => $request->input('selling_price'),
                'inventory' => $request->input('inventory'),
                'is_active' => $request->input('is_active'),
                'min_qty' => 1,
                'added_by' => $vendor->id,
                'is_admin' => 0,
                'date' => now(),
            ];

            if ($imagePath) {
                $data['image'] = json_encode($imagePath); // Store as JSON array
            }

            if (empty($request->input('pro_id'))) {
                // Insert new product
                $product = Product::create($data);
                Log::info('AddVendorProduct: Product created', [
                    'vendor_id' => $vendor->id,
                    'product_id' => $product->id,
                ]);
            } else {
                // Update existing product
                $product = Product::where('id', $request->input('pro_id'))
                                ->where('added_by', $vendor->id)
                                ->where('is_admin', 0)
                                ->first();

                if (!$product) {
                    Log::warning('AddVendorProduct: Product not found or unauthorized', [
                        'vendor_id' => $vendor->id,
                        'pro_id' => $request->input('pro_id'),
                    ]);
                    return response()->json([
                        'message' => 'Product not found or unauthorized!',
                        'status' => 201,
                    ], 404);
                }

                // Preserve existing image if no new image uploaded
                if (!$imagePath) {
                    unset($data['image']);
                }

                $product->update($data);
                Log::info('AddVendorProduct: Product updated', [
                    'vendor_id' => $vendor->id,
                    'product_id' => $product->id,
                ]);
            }

            return response()->json([
                'message' => 'Success',
                'status' => 200,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('AddVendorProduct: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('AddVendorProduct: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing product: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function homeData(Request $request)
    {
        try {
            // Authenticate vendor
            // /** @var \App\Models\Vendor $vendor */
           $token = $request->header('Authentication');
            if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $vendor = Vendor::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$vendor) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Update FCM token
            $fcm_token = $request->header('Fcm-Token', '');
            if ($fcm_token && $fcm_token !== $vendor->fcm_token) {
                // $vendor->update(['fcm_token' => $fcm_token]);
                Log::info('HomeData: FCM token updated', [
                    'vendor_id' => $vendor->id,
                    'fcm_token' => $fcm_token,
                ]);
            }

            $today = now()->format('Y-m-d');

            // Today’s orders
            $today_orders = Order1::where('vendor_id', $vendor->id)
                ->whereIn('payment_status', [1, 2])
                ->where('is_admin', 0)
                ->whereDate('date', $today)
                ->count();

            // New orders
            $new_orders = Order1::where('vendor_id', $vendor->id)
                ->whereIn('payment_status', [1, 2])
                ->where('order_status', 1)
                ->where('is_admin', 0)
                ->count();

            // Accepted orders
            $accepted_orders = Order1::where('vendor_id', $vendor->id)
                ->whereIn('payment_status', [1, 2])
                ->where('order_status', 2)
                ->where('is_admin', 0)
                ->count();

            // Dispatched orders
            $dispatched_orders = Order1::where('vendor_id', $vendor->id)
                ->whereIn('payment_status', [1, 2])
                ->where('order_status', 3)
                ->where('is_admin', 0)
                ->count();

            // Completed orders
            $completed_orders = Order1::where('vendor_id', $vendor->id)
                ->whereIn('payment_status', [1, 2])
                ->where('order_status', 4)
                ->where('is_admin', 0)
                ->count();

            // Rejected orders
            $rejected_orders = Order1::where('vendor_id', $vendor->id)
                ->whereIn('payment_status', [1, 2])
                ->where('order_status', 6)
                ->where('is_admin', 0)
                ->count();

            // Today’s income
            $today_income = PaymentTransaction::where('vendor_id', $vendor->id)
                ->whereNotNull('req_id')
                ->whereDate('date', $today)
                ->sum('cr') ?? 0;

            // Total income
            $total_income = 0;
            $transactions = PaymentTransaction::where('vendor_id', $vendor->id)
                ->whereNotNull('req_id')
                ->get();

            foreach ($transactions as $transaction) {
                if ($transaction->req_id) {
                    $order = Order1::where('id', $transaction->req_id)
                        ->whereIn('payment_status', [1, 2])
                        ->where('order_status', 4)
                        ->where('is_admin', 0)
                        ->first();
                    if ($order) {
                        $total_income += $transaction->cr;
                    }
                }
            }

            // Vendor sliders
           $vendor_sliders = VendorSlider::where('is_active', 1)
    ->get()
    ->map(function ($slider) {
        // Decode JSON if stored as array (even if it's a single image)
        $images = json_decode($slider->image, true);

        // Get the first image if it's an array, else fallback to null
        $imagePath = is_array($images) && count($images) > 0 ? $images[0] : null;

        return [
            'image' => $imagePath ? url($imagePath) : '',
        ];
    })
    ->toArray();

            // Vendor notifications
            $vendor_notifications = VendorNotification::where('vendor_id', $vendor->id)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'name' => $notification->name,
                        'image' => $notification->image ? url($notification->image) : '',
                        'description' => $notification->dsc,
                        'date' => $notification->date->format('d-m-y, g:i a'),
                    ];
                })->toArray();

            $notification_count = VendorNotification::where('vendor_id', $vendor->id)->count();

            $data = [
                'today_orders' => $today_orders,
                'new_orders' => $new_orders,
                'accepted_orders' => $accepted_orders,
                'dispatched_orders' => $dispatched_orders,
                'completed_orders' => $completed_orders,
                'rejected_orders' => $rejected_orders,
                'today_income' => round($today_income, 2),
                'total_income' => round($total_income, 2),
                'vendor_slider' => $vendor_sliders,
                'notification_data' => $vendor_notifications,
                'notification_count' => $notification_count,
            ];

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('HomeData: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
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
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }


    public function getVendorProfile(Request $request)
    {
        try {
            // Authenticate vendor
            // /** @var \App\Models\Vendor $vendor */
            // $vendor = auth('vendor')->user();
            // Log::info('GetProfile: Auth attempt', [
            //     'vendor_id' => $vendor ? $vendor->id : null,
            //     'is_active' => $vendor ? $vendor->is_active : null,
            //     'ip' => $request->ip(),
            // ]);

            // if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
            //     Log::warning('GetProfile: Authentication failed or vendor inactive/unapproved', [
            //         'vendor_id' => $vendor ? $vendor->id : null,
            //     ]);
            //     return response()->json([
            //         'message' => 'Permission Denied!',
            //         'status' => 201,
            //     ], 403);
            // }
            $token = $request->header('Authentication');
            if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $vendor = Vendor::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$vendor) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }
            // Fetch state name
            $state = State::where('id', $vendor->state)->first();

            $data = [
                'name' => $vendor->name,
                'shop_name' => $vendor->shop_name,
                'address' => $vendor->address,
                'district' => $vendor->district,
                'city' => $vendor->city,
                'state' => $state ? $state->state_name : '',
                'state_id' => $vendor->state,
                'pincode' => $vendor->pincode,
                'commission' => $vendor->commission,
                'gst_no' => $vendor->gst_no,
                'aadhar_no' => $vendor->aadhar_no,
                'image' => $vendor->image ? url($vendor->image) : '',
                'pan_number' => $vendor->pan_number,
                'phone' => $vendor->phone,
                'email' => $vendor->email,
                'bank_name' => $vendor->bank_name,
                'bank_phone' => $vendor->bank_phone,
                'bank_ac' => $vendor->bank_ac,
                'ifsc' => $vendor->ifsc,
                'upi' => $vendor->upi,
                'latitude' => $vendor->latitude,
                'longitude' => $vendor->longitude,
            ];

            return response()->json([
                'message' => 'Success!',
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('GetProfile: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('GetProfile: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function updateBankInfo(Request $request)
    {
        try {
            // Authenticate vendor
            // /** @var \App\Models\Vendor $vendor */
            $vendor = auth('vendor')->user();
            Log::info('UpdateBankInfo: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'is_active' => $vendor ? $vendor->is_active : null,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor || !$vendor->is_active || !$vendor->is_approved) {
                Log::warning('UpdateBankInfo: Authentication failed or vendor inactive/unapproved', [
                    'vendor_id' => $vendor ? $vendor->id : null,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'bank_name' => 'required|string|max:255',
                'bank_phone' => 'required|string|max:15',
                'bank_ac' => 'required|string|max:20',
                'ifsc' => 'required|string|max:11',
                'upi' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('UpdateBankInfo: Validation failed', [
                    'vendor_id' => $vendor->id,
                    'errors' => $validator->errors(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Prepare update data
            $data = [
                'bank_name' => $request->input('bank_name'),
                'bank_phone' => $request->input('bank_phone'),
                'bank_ac' => $request->input('bank_ac'),
                'ifsc' => $request->input('ifsc'),
                'upi' => $request->input('upi'),
            ];

            // Update vendor
            $updated = $vendor->update($data);

            if ($updated) {
                Log::info('UpdateBankInfo: Bank info updated', [
                    'vendor_id' => $vendor->id,
                    'data' => $data,
                ]);
                return response()->json([
                    'message' => 'Success',
                    'status' => 200,
                ], 200);
            } else {
                Log::warning('UpdateBankInfo: Update failed', [
                    'vendor_id' => $vendor->id,
                    'data' => $data,
                ]);
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 500);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('UpdateBankInfo: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('UpdateBankInfo: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            // Authenticate vendor
            // /** @var \App\Models\Vendor $vendor */
            $token = $request->header('Authentication');
             if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $vendor = Vendor::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$vendor) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'district' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'state' => 'required|integer|exists:all_states,id',
                'pincode' => 'nullable|string|max:10',
                'shop_name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:1000',
                'gst_no' => 'nullable|string|max:15',
                'aadhar_no' => 'nullable|string|max:12',
                'pan_no' => 'nullable|string|max:10',
                'email' => 'nullable|email|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:25000',
            ]);

            if ($validator->fails()) {
                Log::warning('UpdateProfile: Validation failed', [
                    'vendor_id' => $vendor->id,
                    'errors' => $validator->errors(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Handle image upload
            $imagePath = $vendor->image; // Preserve existing image
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $filename = 'vendor_profile_' . date('YmdHis') . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('vendors/profiles');

                // Create directory if it doesn't exist
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the file to the destination
                $image->move($destinationPath, $filename);
                $imagePath = 'vendors/profiles/' . $filename;

                Log::info('UpdateProfile: Image uploaded', [
                    'vendor_id' => $vendor->id,
                    'image_path' => $imagePath,
                ]);
            }

            // Prepare update data
            $data = [
                'name' => $request->input('name'),
                'district' => $request->input('district'),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'pincode' => $request->input('pincode'),
                'shop_name' => $request->input('shop_name'),
                'address' => $request->input('address'),
                'gst_no' => $request->input('gst_no'),
                'aadhar_no' => $request->input('aadhar_no'),
                'pan_number' => $request->input('pan_no'),
                'email' => $request->input('email'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'image' => $imagePath,
            ];

            // Update vendor
            $updated = $vendor->update($data);

            if ($updated) {
                Log::info('UpdateProfile: Profile updated', [
                    'vendor_id' => $vendor->id,
                    'data' => Arr::except($data, ['image']),
                ]);
                return response()->json([
                    'message' => 'Success',
                    'status' => 200,
                ], 200);
            } else {
                Log::warning('UpdateProfile: Update failed', [
                    'vendor_id' => $vendor->id,
                    'data' => Arr::except($data, ['image']),
                ]);
                return response()->json([
                    'message' => 'Some error occurred!',
                    'status' => 201,
                ], 500);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('UpdateProfile: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('UpdateProfile: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }


    public function storeSlider(Request $request)
    {
        try {
            // Authenticate vendor
            // /** @var \App\Models\Vendor $vendor */
            $token = $request->header('Authentication');
            if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $vendor = Vendor::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$vendor) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Validate inputs
            $validator = Validator::make($request->all(), [
                'id' => 'nullable|integer',
                'image' => 'required|image|mimes:jpg,jpeg,png|max:25000',
            ]);

            if ($validator->fails()) {
                Log::warning('StoreSlider: Validation failed', [
                    'vendor_id' => $vendor->id,
                    'errors' => $validator->errors(),
                ]);
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'status' => 201,
                ], 422);
            }

            // Handle image upload
            $image = $request->file('image');
            if (!$image || !$image->isValid() || $image->getClientOriginalName() === 'undefined') {
                Log::warning('StoreSlider: Invalid or undefined image file', [
                    'vendor_id' => $vendor->id,
                    'client_original_name' => $image ? $image->getClientOriginalName() : 'null',
                ]);
                return response()->json([
                    'message' => 'Invalid or undefined image file. Please upload a valid JPG or PNG file.',
                    'status' => 201,
                ], 422);
            }

            $tempPath = $image->getPathname();
            Log::info('StoreSlider: Temporary file path', [
                'vendor_id' => $vendor->id,
                'temp_path' => $tempPath,
                'exists' => file_exists($tempPath) ? 'yes' : 'no',
            ]);

            // Get file size before moving
            $imageSize = null;
            if (file_exists($tempPath)) {
                $imageSize = filesize($tempPath) / 1024; // Size in KB
            } else {
                Log::warning('StoreSlider: Temporary file not found', [
                    'vendor_id' => $vendor->id,
                    'temp_path' => $tempPath,
                ]);
                return response()->json([
                    'message' => 'Unable to access uploaded file. Please try again.',
                    'status' => 201,
                ], 422);
            }

            $filename = 'vendor_slider_' . date('YmdHis') . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('vendors/sliders');

            // Create directory if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move the file to the destination
            try {
                $image->move($destinationPath, $filename);
            } catch (\Exception $e) {
                Log::error('StoreSlider: Failed to move file', [
                    'vendor_id' => $vendor->id,
                    'error' => $e->getMessage(),
                    'temp_path' => $tempPath,
                ]);
                return response()->json([
                    'message' => 'Failed to process uploaded file: ' . $e->getMessage(),
                    'status' => 201,
                ], 500);
            }

            $imagePath = 'vendors/sliders/' . $filename;

            Log::info('StoreSlider: Image uploaded', [
                'vendor_id' => $vendor->id,
                'image_path' => $imagePath,
                'image_size' => $imageSize,
            ]);

            // Prepare data
            $data = [
                'image1' => $imagePath,
                'vendor_id' => $vendor->id,
                'ip' => $request->ip(),
                'image_size' => $imageSize,
                'is_active' => 1,
                'date' => now(),
            ];

            if ($request->filled('id')) {
                // Check if slider exists and belongs to vendor
                $slider = VendorSlider2::where('id', $request->input('id'))
                    ->where('vendor_id', $vendor->id)
                    ->first();

                if (!$slider) {
                    Log::info('StoreSlider: Invalid or unauthorized slider ID, creating new slider', [
                        'vendor_id' => $vendor->id,
                        'slider_id' => $request->input('id'),
                    ]);
                    // Create new slider
                    $slider = VendorSlider2::create($data);
                    $lastId = $slider->id;
                    $message = 'Slider successfully added';
                } else {
                    // Delete old image
                    if ($slider->image1 && File::exists(public_path($slider->image1))) {
                        File::delete(public_path($slider->image1));
                        Log::info('StoreSlider: Old image deleted', [
                            'vendor_id' => $vendor->id,
                            'old_image' => $slider->image1,
                        ]);
                    }

                    $slider->update($data);
                    $lastId = $slider->id;
                    $message = 'Slider successfully updated';
                }
            } else {
                // Create new slider
                $slider = VendorSlider2::create($data);
                $lastId = $slider->id;
                $message = 'Slider successfully added';
            }

            Log::info('StoreSlider: Slider processed', [
                'vendor_id' => $vendor->id,
                'slider_id' => $lastId,
                'action' => $request->filled('id') ? 'updated' : 'created',
            ]);

            return response()->json([
                'message' => $message,
                'status' => 200,
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('StoreSlider: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('StoreSlider: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing slider: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function viewVendorSliders(Request $request)
    {
        try {
             $token = $request->header('Authentication');
             if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }
            $farmer = Farmer::where('auth', $token)
                ->where('is_active', 1)
                ->first();;
            $vendor = Vendor::where('auth', $token)
                ->where('is_active', 1)
                ->first();;

            Log::info('ViewVendorSliders: Auth attempt', [
                'farmer_id' => $farmer ? $farmer->id : null,
                'vendor_id' => $vendor ? $vendor->id : null,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$farmer && !$vendor) {
                Log::warning('ViewVendorSliders: Authentication failed', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'status' => 201,
                    'message' => 'Authentication token not found',
                ], 401);
            }

            if ($farmer) {
                // Farmers see active sliders
                $sliders = VendorSlider2::where('is_active', 1)->get();

                if ($sliders->isEmpty()) {
                    Log::info('ViewVendorSliders: No sliders found for farmer', [
                        'farmer_id' => $farmer->id,
                    ]);
                    return response()->json([
                        'status' => 201,
                        'data' => 'No sliders found',
                    ], 200);
                }

                $sliderData = $sliders->map(function ($slide) {
                    return [
                        'date' => $slide->date->format('Y-m-d H:i:s'),
                        'image' => $slide->image1 ? url($slide->image1) : '',
                    ];
                })->toArray();

                Log::info('ViewVendorSliders: Sliders retrieved for farmer', [
                    'farmer_id' => $farmer->id,
                    'count' => count($sliderData),
                ]);

                return response()->json([
                    'status' => 200,
                    'data' => $sliderData,
                ], 200);
            }

            if ($vendor) {
                // Vendors see their own sliders
                if (!$vendor->is_active || !$vendor->is_approved) {
                    Log::warning('ViewVendorSliders: Vendor inactive or unapproved', [
                        'vendor_id' => $vendor->id,
                    ]);
                    return response()->json([
                        'message' => 'Permission Denied!',
                        'status' => 201,
                    ], 403);
                }

                $sliders = VendorSlider2::where('vendor_id', $vendor->id)->get();

                if ($sliders->isEmpty()) {
                    Log::info('ViewVendorSliders: No sliders found for vendor', [
                        'vendor_id' => $vendor->id,
                    ]);
                    return response()->json([
                        'status' => 201,
                        'data' => 'No sliders found',
                    ], 200);
                }

                $sliderData = $sliders->map(function ($slide) {
                    return [
                        'id' => $slide->id,
                        'updated_at' => $slide->updated_at->format('Y-m-d H:i:s'),
                        'image' => $slide->image1 ? url($slide->image1) : '',
                        'image_size' => $slide->image_size,
                    ];
                })->toArray();

                Log::info('ViewVendorSliders: Sliders retrieved for vendor', [
                    'vendor_id' => $vendor->id,
                    'count' => count($sliderData),
                ]);

                return response()->json([
                    'status' => 200,
                    'data' => $sliderData,
                ], 200);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('ViewVendorSliders: Database error', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('ViewVendorSliders: General error', [
                'farmer_id' => auth('farmer')->id() ?? null,
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function deleteVendorSlider(Request $request)
    {
        try {

             $token = $request->header('Authentication');
             if (!$token) {
                Log::warning('No bearer token provided');
                return response()->json([
                    'message' => 'Token required!',
                    'status' => 201,
                ], 401);
            }

            $vendor = Vendor::where('auth', $token)
                ->where('is_active', 1)
                ->first();

            if (!$vendor) {
                Log::warning('Invalid or inactive user for token', ['token' => $token]);
                return response()->json([
                    'message' => 'Invalid token or inactive user!',
                    'status' => 201,
                ], 403);
            }

            // Validate form data
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('DeleteVendorSlider: Validation failed', [
                    'ip' => $request->ip(),
                    'errors' => $validator->errors(),
                    'url' => $request->fullUrl(),
                ]);
                return response()->json([
                    'status' => 201,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $sliderId = $request->input('id');

            

            Log::info('DeleteVendorSlider: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'slider_id' => $sliderId,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor) {
                Log::warning('DeleteVendorSlider: Authentication failed', [
                    'ip' => $request->ip(),
                    'slider_id' => $sliderId,
                ]);
                return response()->json([
                    '$i' => 201,
                    'message' => 'Authentication token not found',
                ], 401);
            }

            // Check vendor status
            if (!$vendor->is_active || !$vendor->is_approved) {
                Log::warning('DeleteVendorSlider: Vendor inactive or unapproved', [
                    'vendor_id' => $vendor->id,
                    'slider_id' => $sliderId,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Find the slider
            $slider = VendorSlider2::where('id', $sliderId)
                ->where('vendor_id', $vendor->id)
                ->first();

            if (!$slider) {
                Log::info('DeleteVendorSlider: No slider found or unauthorized', [
                    'vendor_id' => $vendor->id,
                    'slider_id' => $sliderId,
                ]);
                return response()->json([
                    'status' => 201,
                    'message' => 'No slider found or unauthorized',
                ], 404);
            }

            // Delete the image file
            if ($slider->image1 && File::exists(public_path($slider->image1))) {
                File::delete(public_path($slider->image1));
                Log::info('DeleteVendorSlider: Image deleted', [
                    'vendor_id' => $vendor->id,
                    'slider_id' => $sliderId,
                    'image_path' => $slider->image1,
                ]);
            }

            // Delete the slider record
            $slider->delete();

            Log::info('DeleteVendorSlider: Slider deleted successfully', [
                'vendor_id' => $vendor->id,
                'slider_id' => $sliderId,
            ]);

            return response()->json([
                'status' => 200,
                'data' => 'Slider deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DeleteVendorSlider: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'slider_id' => $sliderId,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('DeleteVendorSlider: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'slider_id' => $sliderId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            // Authenticate vendor
            $vendor = auth('vendor')->user();

            Log::info('DeleteAccount: Auth attempt', [
                'vendor_id' => $vendor ? $vendor->id : null,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'url' => $request->fullUrl(),
            ]);

            if (!$vendor) {
                Log::warning('DeleteAccount: Authentication failed', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'status' => 201,
                    'message' => 'Authentication token not found',
                ], 401);
            }

            // Check vendor status
            if (!$vendor->is_active || !$vendor->is_approved) {
                Log::warning('DeleteAccount: Vendor inactive or unapproved', [
                    'vendor_id' => $vendor->id,
                ]);
                return response()->json([
                    'message' => 'Permission Denied!',
                    'status' => 201,
                ], 403);
            }

            // Update vendor to deactivate account
            $updated = $vendor->update(['is_active' => 0]);

            if ($updated) {
                Log::info('DeleteAccount: Account deactivated successfully', [
                    'vendor_id' => $vendor->id,
                ]);
                return response()->json([
                    'message' => 'Account successfully deleted!',
                    'status' => 200,
                ], 200);
            } else {
                Log::warning('DeleteAccount: Update failed', [
                    'vendor_id' => $vendor->id,
                ]);
                return response()->json([
                    'message' => 'Some error occurred!!',
                    'status' => 201,
                ], 500);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('DeleteAccount: Database error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
            ]);
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        } catch (\Exception $e) {
            Log::error('DeleteAccount: General error', [
                'vendor_id' => auth('vendor')->id() ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error processing request: ' . $e->getMessage(),
                'status' => 201,
            ], 500);
        }
    }

    protected function createPagination($current_page, $total_pages)
    {
        $pagination = [];
        $current_page = (int) $current_page;

        for ($i = 1; $i <= $total_pages; $i++) {
            $pagination[] = [
                'page' => $i,
                'is_active' => $i === $current_page,
            ];
        }

        return $pagination;
    }
}
