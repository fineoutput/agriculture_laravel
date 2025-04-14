<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorOrder2 extends Model
{
    protected $table = 'vendor_order2';
    protected $fillable = [
        'id',
        'main_id',
        'product_id',
        'product_name_en',
        'product_name_hi',
        'product_name_pn',
        'image',
        'qty',
        'mrp',
        'selling_price',
        'gst',
        'gst_price',
        'selling_price_wo_gst',
        'total_amount',
        'discount',
        'date',
    ];
}