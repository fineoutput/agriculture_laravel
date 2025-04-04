<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FarmerSlider extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'tbl_farmersliderslider';
    protected $fillable = ['id','image', 'ip', 'added_by', 'is_active'];
}
