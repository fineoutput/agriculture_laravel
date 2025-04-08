<?php

// app/Models/VendorSlider.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSlider extends Model
{
    protected $table = 'tbl_vendorslider';
    protected $fillable = [
        'image', 'ip', 'added_by', 'is_active', 'date'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}