<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpertiseCategory extends Model
{
    protected $table = 'tbl_expertise_category';
    protected $fillable = ['name', 'image', 'image_hindi', 'image_punjabi', 'image_marathi', 'image_gujrati', 'ip', 'added_by', 'is_active', 'date'];
    protected $casts = ['is_active' => 'boolean'];
}