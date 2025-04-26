<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'otp',
        'type',
        'data',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array', // Automatically decode JSON data field
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
