<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentSalePurchase;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EquipmentSalePurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display all sale/purchase records.
     */
    public function viewSalePurchase()
    {
        $sale_purchase = EquipmentSalePurchase::orderBy('id', 'desc')->get();
        $farmer_details = $this->getFarmerDetails($sale_purchase);
        Log::info('Sale/Purchase Data:', $sale_purchase->toArray());

        return view('admin.equipment.equipment_sale_purchase', [
            'user_name' => Auth::guard('admin')->user()->name,
            'sale_purchase' => $sale_purchase,
            'farmer_details' => $farmer_details,
            'heading' => 'All Sale/Purchase List',
        ]);
    }

    /**
     * Display pending sale/purchase records.
     */
    public function farmerSalePurchasePending()
    {
        $sale_purchase = EquipmentSalePurchase::where('status', 0)->orderBy('id', 'desc')->get();
        $farmer_details = $this->getFarmerDetails($sale_purchase);
        Log::info('Pending Sale/Purchase Data:', $sale_purchase->toArray());

        return view('admin.equipment.equipment_sale_purchase', [
            'user_name' => Auth::guard('admin')->user()->name,
            'sale_purchase' => $sale_purchase,
            'farmer_details' => $farmer_details,
            'heading' => 'Pending Sale/Purchase List',
        ]);
    }

    /**
     * Display accepted sale/purchase records.
     */
    public function farmerSalePurchaseAccepted()
    {
        $sale_purchase = EquipmentSalePurchase::where('status', 1)->orderBy('id', 'desc')->get();
        $farmer_details = $this->getFarmerDetails($sale_purchase);
        Log::info('Accepted Sale/Purchase Data:', $sale_purchase->toArray());

        return view('admin.equipment.equipment_sale_purchase', [
            'user_name' => Auth::guard('admin')->user()->name,
            'sale_purchase' => $sale_purchase,
            'farmer_details' => $farmer_details,
            'heading' => 'Accepted Sale/Purchase List',
        ]);
    }

    /**
     * Display completed sale/purchase records.
     */
    public function farmerSalePurchaseCompleted()
    {
        $sale_purchase = EquipmentSalePurchase::where('status', 2)->orderBy('id', 'desc')->get();
        $farmer_details = $this->getFarmerDetails($sale_purchase);
        Log::info('Completed Sale/Purchase Data:', $sale_purchase->toArray());

        return view('admin.equipment.equipment_sale_purchase', [
            'user_name' => Auth::guard('admin')->user()->name,
            'sale_purchase' => $sale_purchase,
            'farmer_details' => $farmer_details,
            'heading' => 'Completed Sale/Purchase List',
        ]);
    }

    /**
     * Display rejected sale/purchase records.
     */
    public function farmerSalePurchaseRejected()
    {
        $sale_purchase = EquipmentSalePurchase::where('status', 3)->orderBy('id', 'desc')->get();
        $farmer_details = $this->getFarmerDetails($sale_purchase);
        Log::info('Rejected Sale/Purchase Data:', $sale_purchase->toArray());

        return view('admin.equipment.equipment_sale_purchase', [
            'user_name' => Auth::guard('admin')->user()->name,
            'sale_purchase' => $sale_purchase,
            'farmer_details' => $farmer_details,
            'heading' => 'Rejected Sale/Purchase List',
        ]);
    }

    /**
     * Update sale/purchase status.
     */
    public function updateSalePurchaseStatus($idd, $t)
    {
        try {
            $id = base64_decode($idd);
            $sale_purchase = EquipmentSalePurchase::findOrFail($id);

            if ($t === 'accept') {
                $status = 1;
            } elseif ($t === 'complete') {
                $status = 2;
            } elseif ($t === 'reject') {
                $status = 3;
            } else {
                throw new \Exception('Invalid status');
            }

            $sale_purchase->update(['status' => $status]);

            return redirect()->back()->with('smessage', 'Status updated successfully');

        } catch (\Exception $e) {
            Log::error('Sale/Purchase Status Update Error: ' . $e->getMessage());
            return redirect()->back()->with('emessage', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to fetch farmer details.
     */
    private function getFarmerDetails($sale_purchase)
    {
        $farmer_details = [];

        foreach ($sale_purchase as $sale) {
            $farmer = Farmer::select('name', 'phone', 'city')
                ->where('id', $sale->farmer_id)
                ->first();

            $farmer_details[$sale->id] = $farmer ?: (object) ['name' => 'Unknown', 'phone' => 'N/A', 'city' => 'N/A'];
        }

        return $farmer_details;
    }
}