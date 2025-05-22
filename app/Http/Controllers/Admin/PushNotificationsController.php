<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class PushNotificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of push notifications.
     */
    public function viewPushNotifications()
    {
        $pushNotifications = PushNotification::orderBy('id', 'desc')->get();

        return view('admin.push_notification.view_pushnotifications', [
            'user_name' => Auth::guard('admin')->user()->name,
            'pushnotifications_data' => $pushNotifications,
        ]);
    }

    /**
     * Show the form for creating a new push notification.
     */
    public function addPushNotifications()
    {
        return view('admin.push_notification.add_pushnotifications', [
            'user_name' => Auth::guard('admin')->user()->name,
        ]);
    }

    /**
     * Store a newly created push notification.
     */
    public function addPushNotificationsData(Request $request, $t)
    {
        $typ = base64_decode($t);

        $request->validate([
            'title' => 'required|string|max:255',
            'App' => 'required|in:1,2', // 1: Vendor, 2: Farmer
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
            'content' => $request->content,
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

        $notification = PushNotification::create($data);

        // Send Firebase notification
        $to = $request->App == 1 ? 'DairyMuneemVendor' : 'DairyMuneemFarmer';
        $imageUrl = isset($data['image']) ? url($data['image']) : null;

        try {
            $this->sendNotification($request->title, $request->content, $to, $imageUrl);
            return redirect()->route('admin.pushnotifications.view')->with('smessage', 'Push notification created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('emessage', 'Error sending notification: ' . $e->getMessage());
        }
    }

    /**
     * Send a Firebase notification to a topic.
     */
    protected function sendNotification($title, $body, $to, $image = null)
    {
        $messaging = app('firebase.messaging');

        $notification = FirebaseNotification::fromArray([
            'title' => $title,
            'body' => $body,
            'image' => $image,
        ]);

        $message = CloudMessage::withTarget('topic', $to)
            ->withNotification($notification);

        $messaging->send($message);
    }
}