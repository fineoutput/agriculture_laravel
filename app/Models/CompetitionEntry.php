<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionEntry extends Model
{
    use HasFactory;

    protected $table = 'tbl_competition_entry';

   protected $fillable = [
    'start_date',
    'end_date',
    'competition_date',
    'state',
    'city',
    'entry_fees',
    'status',
    'time_slot',
    'slot_time'
];  
}
