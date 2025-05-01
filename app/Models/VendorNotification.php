<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorNotification extends Model
{
    use HasFactory;

    protected $table = 'tbl_vendor_notification';
    protected $fillable = ['id', 'vendor_id', 'name', 'image', 'dsc', 'ip', 'date', 'added_by'];
}
