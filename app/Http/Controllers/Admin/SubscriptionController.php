<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionBuy;
use App\Models\CheckMyFeedBuy;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display all subscriptions.
     */
    public function viewSubscription()
    {
        $subscription_data = Subscription::all();
        $user_name = Auth::guard('admin')->user()->name;

        return view('admin.subscription.view_subscription', compact('subscription_data', 'user_name'));
    }

    /**
     * Show the form to add a new subscription.
     */
    public function addSubscription()
    {
        $user_name = Auth::guard('admin')->user()->name;

        return view('admin.subscription.add_subscription', compact('user_name'));
    }

    /**
     * Handle adding or updating subscription data.
     */
    public function addSubscriptionData(Request $request, $t, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'service_name' => 'required|string|max:255',
            'monthly_price' => 'required|numeric|min:0',
            'monthly_description' => 'required|string',
            'monthly_service' => 'required|string',
            'quaterly_price' => 'required|numeric|min:0',
            'quaterly_description' => 'required|string',
            'quaterly_service' => 'required|string',
            'halfyearly_price' => 'required|numeric|min:0',
            'halfyearly_description' => 'required|string',
            'halfyearly_service' => 'required|string',
            'yearly_price' => 'required|numeric|min:0',
            'yearly_description' => 'required|string',
            'yearly_service' => 'required|string',
            'doctor' => 'required|integer|min:0',
            'animals' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('emessage', $validator->errors()->first())->withInput();
        }

        $data = [
            'service_name' => $request->service_name,
            'monthly_price' => $request->monthly_price,
            'monthly_description' => $request->monthly_description,
            'monthly_service' => $request->monthly_service,
            'quarterly_price' => $request->quaterly_price,
            'quarterly_description' => $request->quaterly_description,
            'quarterly_service' => $request->quaterly_service,
            'halfyearly_price' => $request->halfyearly_price,
            'halfyearly_description' => $request->halfyearly_description,
            'halfyearly_service' => $request->halfyearly_service,
            'yearly_price' => $request->yearly_price,
            'yearly_description' => $request->yearly_description,
            'yearly_service' => $request->yearly_service,
            'doctor_calls' => $request->doctor,
            'animals' => $request->animals,
        ];

        try {
            if ($id) {
                Subscription::where('id', $id)->update($data);
            } else {
                Subscription::create($data);
            }

            return redirect()->route('admin.subscription.view_subscription')
                ->with('smessage', 'Data ' . ($id ? 'updated' : 'added') . ' successfully');
        } catch (\Exception $e) {
            Log::error('Subscription save error: ' . $e->getMessage());
            return redirect()->back()->with('emessage', 'An error occurred. Please try again.');
        }
    }

    /**
     * Show the form to edit a subscription.
     */
    public function updateSubscription($id)
    {
        $user_name = Auth::guard('admin')->user()->name;
        $subscription = Subscription::findOrFail(base64_decode($id));
        $idd = $id; // Pass encoded ID for form

        return view('admin.subscription.update_subscription', compact('subscription', 'idd', 'user_name'));
    }

    /**
     * Delete a subscription (Super Admin only).
     */
    public function deleteSubscription($id)
    {
        if (Auth::guard('admin')->user()->position !== 'Super Admin') {
            return view('admin.errors.error500', ['e' => "Sorry, you don't have permission to delete anything."]);
        }

        try {
            Subscription::destroy(base64_decode($id));
            return redirect()->route('admin.subscription.view_subscription');
        } catch (\Exception $e) {
            Log::error('Subscription delete error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Update subscription status (active/inactive).
     */
    public function updateSubscriptionStatus($id, $status)
    {
        $statusValue = $status === 'active' ? 1 : ($status === 'inactive' ? 0 : null);

        if (is_null($statusValue)) {
            return view('admin.errors.error500', ['e' => 'Invalid status']);
        }

        try {
            Subscription::where('id', base64_decode($id))->update(['is_active' => $statusValue]);
            return redirect()->route('admin.subscription.view_subscription');
        } catch (\Exception $e) {
            Log::error('Subscription status update error: ' . $e->getMessage());
            return view('admin.errors.error500', ['e' => 'Error Occurred']);
        }
    }

    /**
     * Get cities by state ID (misnamed as getsubscription).
     */
    public function getCities($state_id)
    {
        $cities = City::where('state_id', $state_id)->get(['id', 'city_name']);

        if ($cities->isEmpty()) {
            return response()->json('NA');
        }

        return response()->json($cities->map(function ($city) {
            return [
                'cities_id' => $city->id,
                'city_name' => $city->city_name,
            ];
        }));
    }

    /**
     * Display subscribed data.
     */
    public function viewSubscribedData()
    {
        $subscription_data = SubscriptionBuy::with([
            'farmer' => function ($query) {
                $query->select('id', 'name', 'phone');
            },
            'plan' => function ($query) {
                $query->select('id', 'service_name');
            }
        ])->where('payment_status', 1)->get();

        $user_name = Auth::guard('admin')->user()->name;

        return view('admin.subscription.view_subscribed', compact('subscription_data', 'user_name'));
    }

    /**
     * Display Check My Feed data.
     */
    public function viewCheckFeed()
    {
        $check_feed_data = CheckMyFeedBuy::with([
            'farmer' => function ($query) {
                $query->select('id', 'name', 'phone');
            }
        ])->where('payment_status', 1)->get();

        $user_name = Auth::guard('admin')->user()->name;

        // Log data for debugging
        Log::info('Check Feed Data:', $check_feed_data->toArray());

        return view('admin.subscription.view_check_feed', compact('check_feed_data', 'user_name'));
    }
}