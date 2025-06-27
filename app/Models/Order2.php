<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order2 extends Model
{
    use HasFactory;

    protected $table = 'tbl_order1';

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
        'date'
    ];

    // public function farmer()
    // {
    //     return $this->belongsTo(Farmer::class, 'farmer_id', 'id');
    // }
   public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id', 'id');
    }

    // public function items()
    // {
    //     return $this->hasMany(OrderItem::class, 'order_id', 'id');
    // }
}
