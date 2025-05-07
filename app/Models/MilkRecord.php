<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkRecord extends Model
{
    use HasFactory;

    protected $table = 'tbl_milk_records';

    protected $fillable = [
        "id",
        "farmer_id",
        "information_type",
        "group_id",
        "tag_no",
        "milking_slot",
        "milk_date",
        "entry_milk",
        "price_milk",
        "fat",
        "snf",
        "total_price",
        "date"
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }
}
