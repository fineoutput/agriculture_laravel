<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSemenTransaction extends Model
{
    protected $table = 'tbl_doctor_semen_txn';
    protected $fillable = [
        'doctor_id',
        'tank_id',
        'canister',
        'bull_name',
        'company_name',
        'no_of_units',
        'sell_unit',
        'milk_production_of_mother',
        'farmer_name',
        'farmer_phone',
        'address',
        'date',
    ];
    public $timestamps = false;

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function tank()
    {
        return $this->belongsTo(DoctorTank::class, 'tank_id', 'id');
    }

    
}