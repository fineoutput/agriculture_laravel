<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class DoctorRequest extends Model
{

    use HasFactory; 
    protected $table = 'tbl_doctor_req';
    protected $fillable = ['doctor_id', 'payment_status', 'fees'];
    protected $casts = [
        'payment_status' => 'integer'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'req_id');
    }
}