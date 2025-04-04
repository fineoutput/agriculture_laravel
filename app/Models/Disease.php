<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;

    protected $table = 'tbl_disease';
    protected $fillable = [
        'title',
        'content',
        'image1',
        'ip',
        'is_active',
        'date',
        'added_by',
        'file',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    
}
