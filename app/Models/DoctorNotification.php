<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorNotification extends Model
{
    use HasFactory;

     protected $table = 'tbl_doctor_notification';
    protected $fillable = [
        'id',
        'doctor_id',
        'name',
        'image',
        'ip',
        'date',
        'added_by',
    ];
}
