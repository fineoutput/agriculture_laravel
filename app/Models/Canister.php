<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canister extends Model
{
    use HasFactory;

    protected $table = 'tbl_canister';

    protected $fillable = [
        "id",
        "farmer_id",
        "tank_id",
        "farm_bull",
        "tag_no",
        "bull_name",
        "company_name",
        "no_of_units",
        "milk_production_of_mother",
        "date"
      ];

      public function farmer()
      {
          return $this->belongsTo(Farmer::class, 'farmer_id');
      }
  
      public function tank()
      {
          return $this->belongsTo(Tank::class, 'tank_id');
      }
}
