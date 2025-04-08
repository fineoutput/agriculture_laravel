<?php

// app/Models/Slider.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $table = 'tbl_slider';
    protected $fillable = [
        'image', 'ip', 'added_by', 'is_active', 'date'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}