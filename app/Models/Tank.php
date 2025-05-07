<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tank extends Model
{
    use HasFactory;

    protected $table = 'tbl_tank';

    protected $fillable = [
        'id',
        'farmer_id',
        'name',
        'date'
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }

    public function canisters()
    {
        return $this->hasMany(Canister::class, 'tank_id');
    }
}
