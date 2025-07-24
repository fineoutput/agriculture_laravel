<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    use HasFactory;

    protected $table = 'tbl_go_live';

   protected $fillable = [
    'live_id',
    'user_id',
    'user_name',
    'status',
    'created_at',
    'updated_at',
    'deleted_at',
]; 
}
