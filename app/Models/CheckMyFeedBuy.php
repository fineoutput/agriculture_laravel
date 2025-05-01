<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckMyFeedBuy extends Model
{
    use HasFactory;
    protected $table = 'tbl_check_my_feed_buy';
    protected $fillable = [
        'farmer_id',
        'price',
        'payment_status',
        'txn_id',
        'date',
        'gateway',
        'cc_response',
    ];
}
