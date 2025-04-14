<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorOrder1;
use App\Models\VendorOrder2;
use App\Models\PaymentTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VendorAppOrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display new orders.
     */
    public function newOrder()
    {
        $order1_data = VendorOrder1::whereIn('payment_status', [1, 2])
            ->where('order_status', 1)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculatePendingAmount($order1_data);

        Log::info('New Orders Data:', $order1_data->toArray());

        return view('admin.vendorapporders.view_vendororder', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $order1_data,
            'count' => $count,
            'heading' => 'New',
            'order_type' => 1,
        ]);
    }

    /**
     * Display accepted orders.
     */
    public function acceptedOrder()
    {
        $order1_data = VendorOrder1::whereIn('payment_status', [1, 2])
            ->where('order_status', 2)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculatePendingAmount($order1_data);

        Log::info('Accepted Orders Data:', $order1_data->toArray());

        return view('admin.vendorapporders.view_vendororder', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $order1_data,
            'count' => $count,
            'heading' => 'Accepted',
            'order_type' => 1,
        ]);
    }

    /**
     * Display dispatched orders.
     */
    public function dispatchedOrder()
    {
        $order1_data = VendorOrder1::where('payment_status', 1)
            ->where('order_status', 3)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculatePendingAmount($order1_data);

        Log::info('Dispatched Orders Data:', $order1_data->toArray());

        return view('admin.vendorapporders.view_vendororder', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $order1_data,
            'count' => $count,
            'heading' => 'Dispatched',
            'order_type' => 1,
        ]);
    }

    /**
     * Display completed orders.
     */
    public function completedOrder()
    {
        $order1_data = VendorOrder1::where('payment_status', 1)
            ->where('order_status', 4)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculatePendingAmount($order1_data);

        Log::info('Completed Orders Data:', $order1_data->toArray());

        return view('admin.vendorapporders.view_vendororder', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $order1_data,
            'count' => $count,
            'heading' => 'Completed',
            'order_type' => 1,
        ]);
    }

    /**
     * Display cancelled orders.
     */
    public function cancelledOrder()
    {
        $order1_data = VendorOrder1::where('payment_status', 1)
            ->where('order_status', '>', 4)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculatePendingAmount($order1_data);

        Log::info('Cancelled Orders Data:', $order1_data->toArray());

        return view('admin.vendorapporders.view_vendororder', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $order1_data,
            'count' => $count,
            'heading' => 'Rejected/Cancelled',
            'order_type' => 1,
        ]);
    }

    /**
     * Display rejected orders.
     */
    public function rejectedOrder()
    {
        $order1_data = VendorOrder1::where('payment_status', 1)
            ->where('order_status', 6)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculatePendingAmount($order1_data);

        Log::info('Rejected Orders Data:', $order1_data->toArray());

        return view('admin.vendorapporders.view_vendororder', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $order1_data,
            'count' => $count,
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
            $order = VendorOrder1::findOrFail($id);

            if ($t === 'accept') {
                $status = 1;
            } elseif ($t === 'complete') {
                $status = 2;
            } elseif ($t === 'reject') {
                $status = 3;
            } else {
                throw new \Exception('Invalid status');
            }

            $order->update(['order_status' => $status]);

            if ($t === 'reject') {
                $order2_data = VendorOrder2::where('main_id', $id)->get();
                foreach ($order2_data as $order2) {
                    $product = Product::find($order2->product_id);
                    if ($product) {
                        $product->update(['inventory' => $product->inventory + $order2->qty]);
                    }
                }
            }

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
            $order1_data = VendorOrder1::findOrFail($id);
            $order2_data = VendorOrder2::where('main_id', $id)->get();

            Log::info('Order Detail Data:', ['order1' => $order1_data->toArray(), 'order2_count' => $order2_data->count()]);

            return view('admin.vendorapporders.order_details', [
                'user_name' => Auth::guard('admin')->user()->name,
                'id' => $idd,
                'order2_data' => $order2_data,
                'status' => $order1_data->order_status,
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
            $order1_data = VendorOrder1::findOrFail($id);
            $order2_data = VendorOrder2::where('main_id', $order1_data->id)->get();

            Log::info('View Bill Data:', ['order1' => $order1_data->toArray(), 'order2_count' => $order2_data->count()]);

            return view('admin.vendorapporders.view_bill', [
                'user_name' => Auth::guard('admin')->user()->name,
                'order1_data' => $order1_data,
                'order2_data' => $order2_data,
            ]);

        } catch (\Exception $e) {
            Log::error('View Bill Error: ' . $e->getMessage());
            return redirect()->route('admin_index')->with('emessage', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Calculate pending amount for orders.
     */
    private function calculatePendingAmount($order1_data)
    {
        $count = 0;

        foreach ($order1_data as $order) {
            $payment_txn = PaymentTransaction::where('req_id', $order->id)
                ->where('vendor_id', $order->vendor_id)
                ->first();

            if ($payment_txn && !empty($payment_txn->cr)) {
                $count += $order->total_amount - $payment_txn->cr;
            }
        }

        return $count;
    }
}