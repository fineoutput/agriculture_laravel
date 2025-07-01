<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Tymon\JWTAuth\Contracts\JWTSubject; 
class DoctorRequest extends Model 

{

    use HasFactory; 
    
    use AuthenticatableTrait;
    protected $table = 'tbl_doctor_req';
    protected $fillable = ['doctor_id',
        'farmer_id',
        'reason',
        'description',
        'is_expert',
        'fees',
        'image1',
        'image2',
        'image3',
        'image4',
        'image5',
        'status',
        'payment_status',
        'date',];
    protected $casts = [
        'payment_status' => 'integer'
    ];

     public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id', 'id');
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'req_id');
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