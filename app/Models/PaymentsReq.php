<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentsReq extends Model
{
    protected $table = 'tbl_payments_req';
    protected $fillable = [
        'id',
        'doctor_id',
        'vendor_id',
        'available',
        'amount',
        'status',
        'date',
    ];
    public $timestamps = false;

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
}