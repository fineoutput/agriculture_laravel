<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    protected $table = 'gift_card';
    protected $fillable = ['amount', 'gift_count', 'start_range', 'end_range', 'image', 'ip', 'added_by', 'is_active', 'allocated', 'date'];
    protected $casts = ['is_active' => 'boolean', 'allocated' => 'boolean'];
}