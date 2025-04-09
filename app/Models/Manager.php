<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manager extends Model
{
    protected $table = 'tbl_manager';
    protected $fillable = ['name', 'phone', 'address', 'email', 'images', 'aadhar', 'refer_code', 'ip', 'is_active', 'added_by', 'date'];
    protected $casts = ['is_active' => 'boolean', 'images' => 'array']; // Cast images as array (JSON)
}