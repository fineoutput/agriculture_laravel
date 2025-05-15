<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MyAnimal extends Model
{
    use HasFactory;
    protected $table = 'tbl_my_animal';
    protected $fillable = [
        "id",
        "animal_type",
        "farmer_id",
        "assign_to_group",
        "tag_no",
        "animal_name",
        "dob",
        "father_name",
        "mother_name",
        "weight",
        "age",
        "breed_type",
        "is_inseminated",
        "semen_brand",
        "insemination_date",
        "insemination_type",
        "animal_gender",
        "is_pregnant",
        "pregnancy_test_date",
        "service_status",
        "in_house",
        "lactation",
        "calving_date",
        "insured_value",
        "insurance_no",
        "renewal_period",
        "insurance_date",
        "dry_date",
        "delivered_date",
        "date",
        "twentyone_days",
        "two_month",
        "seven_month",
        "nine_month"
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }

    public function group()
{
    return $this->belongsTo(Group::class, 'group_id'); // adjust 'group_id' if your foreign key is different
}
}
