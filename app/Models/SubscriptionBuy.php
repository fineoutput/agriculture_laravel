<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SubscriptionBuy extends Model
{
    protected $table = 'tbl_subscription_buy';

    protected $fillable = [
        'id',
        'farmer_id',
        'plan_id',
        'months',
        'price',
        'animals',
        'used_animal',
        'doctor_calls',
        'start_date',
        'expiry_date',
        'payment_status',
        'txn_id',
        'gateway',
        'cc_response',
        'date',
    ];
    public $timestamps = false;
}