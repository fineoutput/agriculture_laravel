<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MilkRanking extends Model
{
    use HasFactory;

    protected $table = 'tbl_ranking';

    protected $fillable = [
        'id',
        'farmer_id',
        'image',
        'competition_id',
        'weight',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function farmer()
{
    return $this->belongsTo(Farmer::class, 'farmer_id', 'id'); // or 'farmer_id' if different
}
}
    