<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table = 'tbl_payment_txn';
    protected $fillable = ['id',
    'req_id',
    'admin_id',
    'doctor_id',
    'vendor_id',
    'dr',
    'cr',];

    public function doctorRequest()
    {
        return $this->belongsTo(DoctorRequest::class, 'req_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}