<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorOrder1 extends Model
{
    protected $table = 'vendor_order1';
    protected $fillable = [
        'id',
        'is_admin',
        'vendor_id',
        'charges',
        'total_amount',
        'final_amount',
        'payment_status',
        'order_status',
        'name',
        'address',
        'city',
        'state',
        'district',
        'pincode',
        'phone',
        'invoice_no',
        'invoice_year',
        'gateway',
        'txn_id',
        'cc_response',
        'date',
    ];
}