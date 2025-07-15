<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegImage extends Model
{
    protected $table = 'reg_image'; 

    protected $fillable = ['image_path', 'is_enabled'];
}