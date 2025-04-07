<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table = 'tbl_payment_txn';
    protected $fillable = ['req_id', 'doctor_id', 'cr'];

    public function doctorRequest()
    {
        return $this->belongsTo(DoctorRequest::class, 'req_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}