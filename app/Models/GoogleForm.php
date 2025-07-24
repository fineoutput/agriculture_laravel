<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleForm extends Model
{
    use HasFactory;

    protected $table = 'google_form';

     protected $fillable = 
     [
    'id',
    'farmer_id',
    'Email',
    'farmer_name',
    'mobile_number',
    'village_Town',
    'district',
    'state',
    'animal_ID',
    'breed',
    'lactation_no',
    'date_of_calving',
    'milk_yield',
    'animal_photo_upload',
    'aadhar_number',
    'farmer_photo_upload',
    'created_at',
    'status'
     ];

}
