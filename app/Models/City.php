<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'all_cities';
    protected $fillable = ['id','city_name','state_id',]; // Adjust based on actual columns
}