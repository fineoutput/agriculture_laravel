<?php

// app/Models/DoctorSlider.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSlider extends Model
{
    protected $table = 'tbl_doctorsliderslider';
    protected $fillable = ['image', 'ip', 'added_by', 'is_active', 'date'];
    protected $casts = ['is_active' => 'boolean'];
}