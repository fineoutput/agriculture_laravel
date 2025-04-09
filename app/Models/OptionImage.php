<?php

// app/Models/OptionImage.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptionImage extends Model
{
    protected $table = 'sale_purchase_option_image';
    protected $fillable = ['image1', 'image2', 'image3', 'image4', 'date', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}