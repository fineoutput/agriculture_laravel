<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompResult extends Model
{
    use HasFactory;

    protected $table = 'tbl_comp_result';

    protected $fillable = [
    'comp_id',
    'farmer_id',
    'farmer_name',
    'img',
    'weight',
    'slot',
    'created_at',
    'updated_at',
    'deleted_at',
];
}
