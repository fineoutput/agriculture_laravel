<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
class Farmer extends Model implements Authenticatable, JWTSubject
{
    use HasFactory;
    use AuthenticatableTrait;
    protected $table = 'tbl_farmers';
    protected $fillable = [
        'auth', 'name', 'village', 'state', 'district', 'city', 'pincode',
        'no_animals', 'phone','doc_type', 'date', 'is_active', 'giftcard_id',
        'cod', 'qty_discount'
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['type' => 'farmer'];
    }
}
