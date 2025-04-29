<?php

// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'tbl_products';
    protected $fillable = [
        'id', 'name_english', 'name_hindi', 'name_punjabi', 'description_english', 'description_hindi',
        'description_punjabi', 'image', 'video', 'mrp', 'selling_price', 'gst', 'gst_price',
        'selling_price_wo_gst', 'inventory', 'suffix', 'tranding_products', 'offer', 'ip',
        'added_by', 'is_active', 'is_admin', 'is_approved', 'show_product', 'vendor_min_qty',
        'vendor_selling_price_wo_gst', 'vendor_gst_price', 'vendor_gst', 'vendor_selling_price',
        'vendor_mrp', 'date', 'cod', 'min_qty'
    ];

    protected $casts = [
        'image' => 'array', // Since images are stored as JSON
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
        'is_approved' => 'integer',
        'cod' => 'boolean'
    ];
}