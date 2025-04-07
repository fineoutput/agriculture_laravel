<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'all_states';
    protected $fillable = ['name']; // Adjust based on your actual columns
}
