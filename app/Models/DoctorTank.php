<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorTank extends Model
{
    protected $table = 'tbl_doctor_tank';
    protected $fillable = [
        'id',
        'doctor_id',
        'name',
        'date'
    ];
    public $timestamps = false;

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function canisters()
    {
        return $this->hasMany(DoctorCanister::class, 'tank_id', 'id');
    }
}