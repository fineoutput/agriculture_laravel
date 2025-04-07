<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'tbl_doctor';
    protected $fillable = [
        'name', 'hi_name', 'pn_name', 'email', 'type', 'degree', 'experience',
        'district', 'hi_district', 'pn_district', 'state', 'city', 'hi_city',
        'pn_city', 'pincode', 'aadhar_no', 'expert_category', 'image',
        'is_approved', 'is_expert', 'is_active', 'commission', 'fees', 'is_active2'
    ];
    protected $casts = [
        'expert_category' => 'array',
        'is_approved' => 'integer',
        'is_expert' => 'boolean',
        'is_active' => 'boolean',
        'is_active2' => 'boolean'
    ];

    public function requests()
    {
        return $this->hasMany(DoctorRequest::class, 'doctor_id');
    }

    public function paymentTransactions()
    {
        return $this->hasManyThrough(PaymentTransaction::class, DoctorRequest::class, 'doctor_id', 'req_id');
    }
}
