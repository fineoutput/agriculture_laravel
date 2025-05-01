<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthInfo extends Model
{
    use HasFactory;

    protected $table = 'tbl_health_info';

    protected $fillable = [
        'id' => '',
        'information_type' => '',
        'group_id' => '',
        'farmer_id' => '',
        'cattle_type' => '',
        'tag_no' => '',
        'vaccination_date' => '',
        'diesse_name' => '',
        'vaccination' => '',
        'medicine' => '',
        'deworming' => '',
        'other1' => '',
        'other2' => '',
        'other3' => '',
        'other4' => '',
        'other5' => '',
        'milk_loss' => '',
        'treatment_cost' => '',
        'date' => '',
    ];
}
