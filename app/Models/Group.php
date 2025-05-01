<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'tbl_group';

    protected $fillable = [
        "id",
        "farmer_id",
        "name",
        "ip",
        "date",
        "is_active",
        "added_by"
      ];
 }
