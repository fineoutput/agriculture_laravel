<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcategoryImages extends Model
{
    protected $table = 'tbl_subcategory_images';
    protected $fillable = [
        'image',
        'image_hindi',
        'image_punjabi',
    ];
}