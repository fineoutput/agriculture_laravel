<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterTemp extends Model
{
    use HasFactory;
    protected $table = 'tbl_register_temp';
    protected $fillable = [
        "id",
    "type",
    "name",
    "village",
    "district",
    "city",
    "state",
    "pincode",
    "phone",
    "refer_code",
    "email",
    "image",
    "doc_type",
    "degree",
    "experience",
    "qualification",
    "shop_name",
    "address",
    "gst",
    "pan_no",
    "aadhar_no",
    "latitude",
    "longitude",
    "no_animals",
    "ip",
    "date",
    "expert_category"
    ];
}
