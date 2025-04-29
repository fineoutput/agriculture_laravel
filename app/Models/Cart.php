<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $table = 'tbl_cart';

    protected $fillable = [
        'farmer_id', 'vendor_id', 'product_id', 'is_admin', 'qty', 'date'
    ];
}
