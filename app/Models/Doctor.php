<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Tymon\JWTAuth\Contracts\JWTSubject; 
class Doctor extends Model implements Authenticatable, JWTSubject
{
    use HasFactory;
    use AuthenticatableTrait;
    protected $table = 'tbl_doctor';
    protected $fillable = [
        'name', 'hi_name', 'pn_name', 'email', 'type', 'degree', 'experience',
        'district', 'hi_district', 'pn_district', 'state', 'city', 'hi_city',
        'pn_city', 'pincode', 'aadhar_no', 'expert_category', 'image','bank_name','bank_phone','bank_ac','ifsc','upi',  
        'is_approved', 'is_expert', 'is_active', 'commission', 'fees', 'is_active2','longitude', 'latitude','fcm_token'
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

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['type' => 'doctor'];
    }
}
