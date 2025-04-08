<?php

// app/Models/SalePurchaseSlider.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePurchaseSlider extends Model
{
    protected $table = 'tbl_sale_purchase_slider';
    protected $fillable = [
        'image', 'eq_image', 'ip', 'added_by', 'is_active', 'date'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}