<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'all_states';
    protected $fillable = ['id','state_name']; // Adjust based on your actual columns
}
