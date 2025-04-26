<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryImages extends Model
{
    protected $table = 'tbl_category_images';
    protected $fillable = [
        'id',
        'name',
        'image',
        'image_hindi',
        'image_punjabi',
        'image_marathi',
        'image_gujrati',
        'is_active',
    ];
}