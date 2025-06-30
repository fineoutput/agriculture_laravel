<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorCanister extends Model
{
    protected $table = 'tbl_doctor_canister';
    protected $fillable = [
        'id',
        'doctor_id',
        'tank_id',
        'bull_name','company_name',	'no_of_units',	'milk_production_of_mother','date'	
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