<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHandling extends Model
{
    use HasFactory;

    protected $table = 'tbl_stock_handling';

    protected $fillable = [
        "id",
        "farmer_id",
        "stock_date",
        "green_forage",
        "dry_fodder",
        "silage",
        "cake",
        "grains",
        "bioproducts",
        "churi",
        "oil_seeds",
        "minerals",
        "bypass_fat",
        "toxins",
        "buffer",
        "yeast",
        "calcium",
        "is_txn",
        "date"
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }
}
