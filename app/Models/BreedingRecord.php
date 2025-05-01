<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreedingRecord extends Model
{
    use HasFactory;

    protected $table = 'tbl_breeding_record';
    protected $fillable = [
        'farmer_id',
        'group_id',
        'cattle_type',
        'tag_no',
        'breeding_date',
        'weight',
        'date_of_ai',
        'farm_bull',
        'bull_tag_no',
        'bull_name',
        'expenses',
        'vet_name',
        'update_bull_semen',
        'semen_bull_id',
        'is_pregnant',
        'pregnancy_test_date',
        'date',
        'only_date',
    ];
}
