<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnimalSalePurchase extends Model
{
    protected $table = 'tbl_sale_purchase';
    protected $fillable = ['id',
    'farmer_id',
    'information_type',
    'animal_name',
    'milk_production',
    'lactation',
    'location',
    'pastorate_pregnant',
    'expected_price',
    'image1',
    'image2',
    'image3',
    'image4',
    'video',
    'status',
    'animal_type',
    'description',
    'remarks'];
}