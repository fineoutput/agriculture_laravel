<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PushNotificationsController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->middleware('auth:admin');
        $this->firebaseService = $firebaseService;
    }

    public function viewPushNotifications()
    {
        $pushNotifications = PushNotification::orderBy('id', 'desc')->get();

        return view('admin.push_notification.view_pushnotifications', [
            'user_name' => Auth::guard('admin')->user()->name,
            'pushnotifications_data' => $pushNotifications,
        ]);
    }

    public function addPushNotifications()
    {
        return view('admin.push_notification.add_pushnotifications');
    }

   public function addPushNotificationsData(Request $request)
    {
        // Your existing code from the previous message
        $typ = base64_decode($request->input('t'));

        $request->validate([
            'title' => 'required|string|max:255',
            'App' => 'required|in:1,2',
            'content' => 'nullable|string',
            'image' => ($typ == 1) ? 'required|image|mimes:jpg,jpeg,png|max:25000' : 'nullable',
        ], [
            'title.required' => 'The title field is required.',
            'App.required' => 'The app selection is required.',
            'image.required' => 'An image is required when type is 1.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a jpg, jpeg, or png file.',
            'image.max' => 'The image may not be larger than 25MB.',
        ]);

        $data = [
            'title' => $request->title,
            'App' => $request->App,
            'content' => strip_tags($request->content),
            'ip' => $request->ip(),
            'added_by' => Auth::guard('admin')->id(),
            'date' => now(),
        ];

        if ($typ == 1 && $request->hasFile('image')) {
            $image = $request->file('image');
            $newFileName = 'Pushnotifications' . now()->format('YmdHis') . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('pushnotifications', $newFileName, 'public');
            $data['image'] = 'storage/' . $path;
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $notification = PushNotification::create($data);
            $to = $request->App == 1 ? 'DairyMuneemVendor' : 'DairyMuneemFarmer';
            $imageUrl = isset($data['image']) ? url($data['image']) : null;

            $result = $this->firebaseService->sendNotificationToTopic($to, $request->title, $request->content, $imageUrl);

            if (!$result['success']) {
                throw new \Exception($result['error']);
            }

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('admin.pushnotifications.view')->with('smessage', 'Push notification created successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Log::error('FCM Notification Error: ' . $e->getMessage(), ['data' => $data]);
            return redirect()->back()->with('emessage', 'Error sending notification: ' . $e->getMessage());
        }
    }
    
}