<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\City;
use App\Models\State;
use App\Models\HealthInfo;
use App\Models\BreedingRecord;
use App\Models\DailyRecord;
use App\Models\MilkRecord;
use App\Models\MedicalExpense;
use App\Models\SalePurchase;
use App\Models\StockHandling;
use App\Models\Tank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FarmersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of farmers.
     */
    public function viewFarmers()
    {
        $farmers_data = Farmer::all();
        Log::info('Farmers Data:', $farmers_data->toArray());

        return view('admin.farmers.index', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmers_data' => $farmers_data,
        ]);
    }

    /**
     * Show the form for adding a new farmer.
     */
    public function addFarmers()
    {
        return view('admin.farmers.add_farmers', [
            'user_name' => Auth::guard('admin')->user()->name,
            'city_data' => City::all(),
            'state_data' => State::all(),
        ]);
    }

    /**
     * Store or update farmer data.
     */
    public function addFarmersData(Request $request, $t, $iw = null)
    {
        try {
            $typ = base64_decode($t);
            $idw = $iw ? base64_decode($iw) : null;

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'village' => 'required|string',
                'district' => 'required|string',
                'state' => 'required|string',
                'city' => 'required|string',
                'Pincode' => 'required|string',
                'phone_number' => 'required|string',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
            }

            $data_insert = [
                'name' => $request->name,
                'village' => $request->village,
                'district' => $request->district,
                'city' => $request->city,
                'state' => $request->state,
                'Pincode' => $request->Pincode,
                'phone_number' => $request->phone_number,
                'ip' => $request->ip(),
                'added_by' => Auth::guard('admin')->id(),
                'date' => Carbon::now('Asia/Kolkata'),
            ];

            if ($typ == 1) {
                $data_insert['is_active'] = 1;
                Farmer::create($data_insert);
            } elseif ($typ == 2 && $idw) {
                Farmer::where('id', $idw)->update($data_insert);
            } else {
                throw new \Exception('Invalid operation type or ID');
            }

            return redirect()->route('admin.farmers.view')->with('smessage', 'Data inserted successfully');
        } catch (\Exception $e) {
            Log::error('Farmer Add/Update Error: ' . $e->getMessage());
            return redirect()->back()->with('emessage', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing a farmer.
     */
    public function updateFarmers($idd)
    {
        $id = base64_decode($idd);
        $farmer = Farmer::findOrFail($id);

        return view('admin.farmers.update_farmers', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmers' => $farmer,
            'id' => $idd,
            'state_data' => State::all(),
            'city_data' => City::all(),
        ]);
    }

    /**
     * Delete a farmer.
     */
    public function deleteFarmers($idd)
    {
        try {
            if (Auth::guard('admin')->user()->position !== 'Super Admin') {
                return view('errors.error500admin', ['e' => "Sorry You Don't Have Permission To Delete Anything."]);
            }

            $id = base64_decode($idd);
            Farmer::findOrFail($id)->delete();

            return redirect()->route('admin.farmers.view')->with('smessage', 'Farmer deleted successfully');
        } catch (\Exception $e) {
            Log::error('Farmer Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('emessage', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Update farmer status (active/inactive).
     */
    public function updateFarmersStatus($idd, $t)
    {
        try {
            $id = base64_decode($idd);
            $status = $t === 'active' ? 1 : ($t === 'inactive' ? 0 : ('Invalid status'));

            Farmer::where('id', $id)->update(['is_active' => $status]);

            return redirect()->route('admin.farmers.index')->with('smessage', 'Status updated successfully');
        } catch (\Exception $e) {
            Log::error('Farmer Status Update Error: ' . $e->getMessage());
            return view('errors.error500admin', ['e' => 'Error Occurred']);
        }
    }

    /**
     * Get cities by state ID (AJAX).
     */
    public function getFarmers($state_id)
    {
        try {
            $cities = City::where('state_id', $state_id)->get();
            if ($cities->isEmpty()) {
                return response()->json('NA', 200);
            }

            $arr = $cities->map(function ($city) {
                return [
                    'cities_id' => $city->id,
                    'city_name' => $city->city_name,
                ];
            })->toArray();

            return response()->json($arr);
        } catch (\Exception $e) {
            Log::error('Get Cities Error: ' . $e->getMessage());
            return response()->json('NA', 500);
        }
    }

    /**
     * Display farmer records overview.
     */
    public function viewRecords($idd)
    {
        $id = base64_decode($idd);

        return view('admin.farmers.view_records', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'health_info' => HealthInfo::where('farmer_id', $id)->count(),
            'breeding_record' => BreedingRecord::where('farmer_id', $id)->count(),
            'daily_records' => DailyRecord::where('farmer_id', $id)->count(),
            'milk_records' => MilkRecord::where('farmer_id', $id)->count(),
            'medical_expenses' => MedicalExpense::where('farmer_id', $id)->count(),
            'sale_purchase' => SalePurchase::where('farmer_id', $id)->count(),
            'stock_handling' => StockHandling::where('farmer_id', $id)->count(),
            'tank' => Tank::where('farmer_id', $id)->count(),
        ]);
    }

    /**
     * Display farmer health info.
     */
    public function viewHealthInfo($idd)
    {
        $id = base64_decode($idd);
        $data_health_info = HealthInfo::where('farmer_id', $id)->get();

        return view('admin.farmers.view_health_info', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_health_info' => $data_health_info,
        ]);
    }

    /**
     * Display farmer breeding records.
     */
    public function viewBreedingRecord($idd)
    {
        $id = base64_decode($idd);
        $data_breeding_record = BreedingRecord::where('farmer_id', $id)->get();

        return view('admin.farmers.view_breeding_records', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_breeding_record' => $data_breeding_record,
        ]);
    }

    /**
     * Display farmer daily records.
     */
    public function viewDailyRecords($idd, Request $request)
    {
        $id = base64_decode($idd);
        $query = DailyRecord::select('entry_id')->distinct()->where('farmer_id', $id)->where('entry_id', '!=', 0);

        if ($request->has(['start_date', 'end_date']) && $request->start_date && $request->end_date) {
            $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
            $end_date = Carbon::parse($request->end_date)->format('Y-m-d');
            $query->whereBetween('record_date', [$start_date, $end_date]);
        }

        $data_daily_records = $query->get();

        return view('admin.farmers.view_daily_records', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_daily_records' => $data_daily_records,
        ]);
    }

    /**
     * Display details of a specific daily record.
     */
    public function viewDetailsDr($idd, $eid)
    {
        $id = base64_decode($idd);
        $eids = base64_decode($eid);
        $data_daily_records = DailyRecord::where('entry_id', $eids)->get();

        return view('admin.farmers.view_detailsdr', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_daily_records' => $data_daily_records,
        ]);
    }

    /**
     * Display farmer milk records.
     */
    public function viewMilkRecords($idd)
    {
        $id = base64_decode($idd);
        $data_milk_records = MilkRecord::where('farmer_id', $id)->get();

        return view('admin.farmers.view_milk_records', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_milk_records' => $data_milk_records,
        ]);
    }

    /**
     * Display farmer medical expenses.
     */
    public function viewMedicalExpenses($idd)
    {
        $id = base64_decode($idd);
        $data_medical_expenses = MedicalExpense::where('farmer_id', $id)->get();

        return view('admin.farmers.view_medical_expenses', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_medical_expenses' => $data_medical_expenses,
        ]);
    }

    /**
     * Display farmer sale/purchase records.
     */
    public function viewSalePurchase($idd)
    {
        $id = base64_decode($idd);
        $data_sale_purchase = SalePurchase::where('farmer_id', $id)->get();

        return view('admin.farmers.view_sale_purchase', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_sale_purchase' => $data_sale_purchase,
        ]);
    }

    /**
     * Display farmer stock handling records.
     */
    public function viewStockHandling($idd)
    {
        $id = base64_decode($idd);
        $data_stock_handling = StockHandling::where('farmer_id', $id)->get();

        return view('admin.farmers.view_stock_list', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_stock_handling' => $data_stock_handling,
        ]);
    }

    /**
     * Display farmer semen tank records.
     */
    public function viewTank($idd)
    {
        $id = base64_decode($idd);
        $data_tank = Tank::where('farmer_id', $id)->get();

        return view('admin.farmers.view_seman_tank_list', [
            'user_name' => Auth::guard('admin')->user()->name,
            'farmer_id' => $idd,
            'data_tank' => $data_tank,
        ]);
    }

    /**
     * Update COD status via AJAX.
     */
    public function storeCodData(Request $request, $id)
    {
       try {
        $farmer = Farmer::findOrFail($id);
        $cod = $request->has('cod') ? 1 : 0; // Checkbox checked = 1, unchecked = 0

        $farmer->update(['cod' => $cod]);

        return redirect()->back()->with('smessage', 'COD status updated successfully');
    } catch (\Exception $e) {
        Log::error('COD Update Error: ' . $e->getMessage());
        return redirect()->back()->with('emessage', 'Error updating COD status: ' . $e->getMessage());
    }
    }

    /**
     * Update quantity discount via AJAX.
     */
    public function qtyUpdate(Request $request)
    {
        try {
            $user_id = $request->input('userId');
            $qty_discount = $request->input('qtyDiscount');

            if (!$user_id || $qty_discount === null) {
                return response()->json(['status' => 'error', 'message' => 'Invalid data received'], 400);
            }

            Farmer::where('id', $user_id)->update(['qty_discount' => $qty_discount]);

            return response()->json(['status' => 'success', 'message' => 'Discount updated successfully']);
        } catch (\Exception $e) {
            Log::error('Quantity Discount Update Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error updating discount'], 500);
        }
    }
}