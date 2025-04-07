<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertiseCategory extends Model
{
    protected $table = 'tbl_expertise_category';
    protected $fillable = ['name', 'is_active']; // Adjust based on your actual columns
    protected $casts = [
        'is_active' => 'boolean'
    ];
}