<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryImages extends Model
{
    protected $table = 'tbl_category_images';
    protected $fillable = [
        'image',
        'image_hindi',
        'image_punjabi',
        'is_active',
    ];
}