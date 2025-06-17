<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Exception\MessagingException;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    public function sendNotificationToTopic($topic, $title, $body, $imageUrl = null)
    {
        try {
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create($title, $body))
                ->withData(['image' => $imageUrl ?? '']); // Send image URL as data payload

            if ($imageUrl) {
                $message = $message->withAndroidConfig([
                    'notification' => [
                        'image' => $imageUrl,
                    ],
                ])->withApnsConfig([
                    'payload' => [
                        'aps' => [
                            'mutable-content' => 1,
                        ],
                    ],
                    'fcm_options' => [
                        'image' => $imageUrl,
                    ],
                ]);
            }

            $this->messaging->send($message);

            return ['success' => true];
        } catch (MessagingException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}