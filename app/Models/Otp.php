<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;
    protected $table = 'tbl_otp';
    protected $fillable = [
        'phone',
        'otp',
        'type',
        'status',
        'data',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array', // Automatically decode JSON data field
        'status' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
