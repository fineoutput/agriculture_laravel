<?php

// app/Models/VendorSlider.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSlider2 extends Model
{
    protected $table = 'tbl_sliders_vender';
    protected $fillable = [
        'id', 'vendor_id', 'image1', 'image_size', 'ip', 'date', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}