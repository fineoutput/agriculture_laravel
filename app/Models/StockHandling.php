<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHandling extends Model
{
    use HasFactory;

    protected $table = 'tbl_stock_handling';

    protected $fillable = [
        'farmer_id',
        'feed',
        'date',
        'is_txn',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }
}
