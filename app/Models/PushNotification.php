<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    protected $table = 'tbl_pushnotifications';

    protected $fillable = [
        'title',
        'App',
        'image',
        'content',
        'ip',
        'added_by',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}