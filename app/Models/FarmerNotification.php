<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerNotification extends Model
{
    use HasFactory;

    protected $table = 'tbl_farmer_notification';

    protected $fillable = [
        'id',
        'farmer_id',
        'name',
        'image',
        'dsc',
        'ip',
        'date',
        'is_active',
        'added_by',
    ];    
}
