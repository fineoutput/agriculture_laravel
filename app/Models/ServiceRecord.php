<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
class ServiceRecord extends Model implements Authenticatable, JWTSubject
{
    use HasFactory;

    use AuthenticatableTrait;
    protected $table = 'tbl_service_records';
    protected $fillable = [
        'id',
        'weight_calculator',
        'dmi_calculator',
        'animal_req',
        'feed_calculator',
        'silage_making',
        'preg_calculator',
        'thi_calculator',
        'pro_req',
        'snf_calculator',
        'check_my_feed'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['type' => 'farmer'];
    }
    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id', 'id');
    }
}
