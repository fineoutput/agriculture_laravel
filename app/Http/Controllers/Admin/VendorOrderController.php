<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order1;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VendorOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display new vendor orders.
     */
    public function newOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', 1)
            ->where('is_admin', 0)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculateVendorEarnings($orders);

        Log::info('New Vendor Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'count' => $count,
            'heading' => 'New',
            'order_type' => 1,
        ]);
    }

    /**
     * Display accepted vendor orders.
     */
    public function acceptedOrder()
    {
        $orders = Order1::whereIn('payment_status', [1, 2])
            ->where('order_status', 2)
            ->where('is_admin', 0)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculateVendorEarnings($orders);

        Log::info('Accepted Vendor Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'count' => $count,
            'heading' => 'Accepted',
            'order_type' => 1,
        ]);
    }

    /**
     * Display dispatched vendor orders.
     */
    public function dispatchedOrder()
    {
        $orders = Order1::where('payment_status', 1)
            ->where('order_status', 3)
            ->where('is_admin', 0)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculateVendorEarnings($orders);

        Log::info('Dispatched Vendor Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'count' => $count,
            'heading' => 'Dispatched',
            'order_type' => 1,
        ]);
    }

    /**
     * Display completed vendor orders.
     */
    public function completedOrder()
    {
        $orders = Order1::where('payment_status', 1)
            ->where('order_status', 4)
            ->where('is_admin', 0)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculateVendorEarnings($orders);

        Log::info('Completed Vendor Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'count' => $count,
            'heading' => 'Completed',
            'order_type' => 1,
        ]);
    }

    /**
     * Display cancelled vendor orders.
     */
    public function cancelledOrder()
    {
        $orders = Order1::where('payment_status', 1)
            ->where('order_status', '>', 4)
            ->where('is_admin', 0)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculateVendorEarnings($orders);

        Log::info('Cancelled Vendor Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'count' => $count,
            'heading' => 'Rejected/Cancelled',
            'order_type' => 1,
        ]);
    }

    /**
     * Display rejected vendor orders.
     */
    public function rejectedOrder()
    {
        $orders = Order1::where('payment_status', 1)
            ->where('order_status', 6)
            ->where('is_admin', 0)
            ->orderBy('id', 'desc')
            ->get();

        $count = $this->calculateVendorEarnings($orders);

        Log::info('Rejected Vendor Orders Data:', $orders->toArray());

        return view('admin.orders.view_order', [
            'user_name' => Auth::guard('admin')->user()->name,
            'order1_data' => $orders,
            'count' => $count,
            'heading' => 'Rejected',
            'order_type' => 1,
        ]);
    }

    /**
     * Calculate total vendor earnings from orders.
     */
    protected function calculateVendorEarnings($orders)
    {
        $count = 0;
        foreach ($orders as $order) {
            $payment_txn = PaymentTransaction::where('req_id', $order->id)
                ->where('vendor_id', $order->vendor_id)
                ->first();
            if ($payment_txn && $payment_txn->cr) {
                $count += $order->total_amount - $payment_txn->cr;
            }
        }
        return $count;
    }
}