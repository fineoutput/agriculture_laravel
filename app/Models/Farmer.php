<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory;

    protected $table = 'tbl_farmers';
    protected $fillable = [
        'name', 'village', 'state', 'district', 'city', 'pincode',
        'no_animals', 'phone', 'date', 'is_active', 'giftcard_id',
        'cod', 'qty_discount'
    ];
}
