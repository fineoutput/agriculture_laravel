<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
class ServiceRecordTxn extends Model implements Authenticatable, JWTSubject
{
    use HasFactory;

    use AuthenticatableTrait;
    protected $table = 'tbl_service_records_txn';
    
    protected $fillable = [
        'id',
    'farmer_id',
    'service',
    'ip',
    'date',
    'only_date'
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
