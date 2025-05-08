<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnimalCycle extends Model
{
    protected $table = 'tbl_animal_cycle';

    protected $fillable = [
        'record_date',
        'farmer_id',
        'animal_id',
        'status',
        'date',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }

    public function animal()
    {
        return $this->belongsTo(MyAnimal::class, 'animal_id');
    }
}
