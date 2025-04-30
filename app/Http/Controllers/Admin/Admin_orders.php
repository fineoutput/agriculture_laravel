<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order1;
use App\Models\Order2;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Admin_orders extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display all orders.
     */
    public function allOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('is_admin', 1)
            ->orderBy('id', 'desc')
            ->get();
        Log::info('All Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'heading' => 'Total',
            'order_type' => 1,
        ]);
    }

    /**
     * Display new orders.
     */
    public function newOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', 1)
            ->where('is_admin', 1)
            ->orderBy('id', 'desc')
            ->get();
        Log::info('New Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'heading' => 'New',
            'order_type' => 1,
        ]);
    }

    /**
     * Display accepted orders.
     */
    public function acceptedOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', 2)
            ->where('is_admin', 1)
            ->orderBy('id', 'desc')
            ->get();
        Log::info('Accepted Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'heading' => 'Accepted',
            'order_type' => 1,
        ]);
    }

    /**
     * Display today's orders.
     */
    public function todayOrder()
    {
        $cur_date = now()->format('Y-m-d');
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', 1)
            ->where('is_admin', 1)
            ->whereDate('date', $cur_date)
            ->orderBy('id', 'desc')
            ->get();
        Log::info('Today Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'heading' => 'Today Orders',
            'order_type' => 1,
        ]);
    }

    /**
     * Display dispatched orders.
     */
    public function dispatchedOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', 3)
            ->where('is_admin', 1)
            ->orderBy('id', 'desc')
            ->get();
        Log::info('Dispatched Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'heading' => 'Dispatched',
            'order_type' => 1,
        ]);
    }

    /**
     * Display completed orders.
     */
    public function completedOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', 4)
            ->where('is_admin', 1)
            ->orderBy('id', 'desc')
            ->get();
        Log::info('Completed Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'heading' => 'Completed',
            'order_type' => 1,
        ]);
    }

    /**
     * Display cancelled/rejected orders.
     */
    public function cancelledOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', '>', 4)
            ->where('is_admin', 1)
            ->orderBy('id', 'desc')
            ->get();
        Log::info('Cancelled Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'heading' => 'Rejected/Cancelled',
            'order_type' => 1,
        ]);
    }

    /**
     * Display rejected orders.
     */
    public function rejectedOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', 6)
            ->where('is_admin', 1)
            ->orderBy('id', 'desc')
            ->get();
        Log::info('Rejected Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'heading' => 'Rejected',
            'order_type' => 1,
        ]);
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus($idd, $t)
    {
        try {
            $id = base64_decode($idd);
            $order = Order1::where('is_admin', 1)->findOrFail($id);

            if ($t === 'confirmed') {
                $status = 2;
            } elseif ($t === 'dispatched') {
                $status = 3;
            } elseif ($t === 'completed') {
                $status = 4;
            } elseif ($t === 'reject') {
                $status = 6;
            } else {
                throw new \Exception('Invalid status');
            }

            if ($t === 'reject') {
                // Update inventory
                $order2_items = Order2::where('main_id', $id)->get();
                foreach ($order2_items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->inventory += $item->qty;
                        $product->save();
                    }
                }
            }

            $order->update(['order_status' => $status]);

            return redirect()->back()->with('smessage', 'Status updated successfully');
        } catch (\Exception $e) {
            Log::error('Order Status Update Error: ' . $e->getMessage());
            return redirect()->back()->with('emessage', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display order details.
     */
    public function orderDetail($idd, $t = '')
    {
        try {
            $id = base64_decode($idd);
            $order2_data = Order2::where('main_id', $id)->get();
            $order1 = Order1::where('id', $id)->firstOrFail();

            return view('admin.orders.order_details', [
                'user_name' => Auth::guard('admin')->user()->name,
                'order2_data' => $order2_data,
                'id' => $idd,
                'status' => $order1->order_status,
            ]);
        } catch (\Exception $e) {
            Log::error('Order Detail Error: ' . $e->getMessage());
            return redirect()->route('admin_index')->with('emessage', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display order bill.
     */
    public function viewBill($idd)
    {
        try {
            $id = base64_decode($idd);
            $order1_data = Order1::where('id', $id)->firstOrFail();
            $order2_data = Order2::where('main_id', $id)->get();

            return view('admin.orders.view_bill', [
                'user_name' => Auth::guard('admin')->user()->name,
                'order1_data' => $order1_data,
                'order2_data' => $order2_data,
                'id' => $idd,
            ]);
        } catch (\Exception $e) {
            Log::error('View Bill Error: ' . $e->getMessage());
            return redirect()->route('admin_index')->with('emessage', 'Error: ' . $e->getMessage());
        }
    }
}