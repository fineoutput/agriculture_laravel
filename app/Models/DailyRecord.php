<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRecord extends Model
{
   
    protected $table = 'tbl_daily_records';

    protected $fillable = [
        'record_date',
        'farmer_id',
        'entry_id',
        'name',
        'type',
        'qty',
        'price',
        'amount',
        'update_inventory',
        'date',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }
}
