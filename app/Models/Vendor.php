<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'tbl_vendor';
    protected $fillable = [
        'name', 'hi_name', 'pn_name', 'shop_name', 'shop_hi_name', 'shop_pn_name',
        'address', 'hi_address', 'pn_address', 'district', 'hi_district', 'pn_district',
        'city', 'hi_city', 'pn_city', 'state', 'pincode', 'gst_no', 'aadhar_no', 'pan_number',
        'phone', 'email', 'image', 'added_by', 'is_active', 'is_approved', 'date',
        'cod', 'comission', 'qty_discount'
    ];
    protected $casts = ['is_active' => 'boolean', 'is_approved' => 'integer', 'cod' => 'boolean'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return ['type' => 'vendor'];
    }
}