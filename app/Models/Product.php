<?php

// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'tbl_products';
    protected $fillable = [
        'id',
    'name_english',
    'name_hindi',
    'name_punjabi',
    'name_marathi',
    'description_english',
    'description_hindi',
    'description_punjabi',
    'description_marathi',
    'image',
    'video',
    'mrp',
    'selling_price',
    'gst',
    'gst_price',
    'selling_price_wo_gst',
    'inventory',
    'suffix',
    'tranding_products',
    'offer',
    'ip',
    'date',
    'is_active',
    'is_admin',
    'is_approved',
    'cod',
    'added_by',
    'min_qty',
    'vendor_mrp',
    'vendor_selling_price',
    'vendor_gst',
    'vendor_gst_price',
    'vendor_selling_price_wo_gst',
    'vendor_min_qty',
    'show_product',
    'created_at',
    'updated_at',
    'deleted_at',
    ];

    protected $casts = [
        'image' => 'array', // Since images are stored as JSON
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
        'is_approved' => 'integer',
        'cod' => 'boolean'
    ];
}