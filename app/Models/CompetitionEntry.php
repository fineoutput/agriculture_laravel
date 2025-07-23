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
    'judge',
    'slot_time'
];


public function doctor()
{
    return $this->belongsTo(Doctor::class, 'judge');
}


public function getCityNamesAttribute()
{
    if (!$this->city) return [];

    $ids = explode(',', $this->city);
    return City::whereIn('id', $ids)->pluck('city_name')->toArray();
}
}
