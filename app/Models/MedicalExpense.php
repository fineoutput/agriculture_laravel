<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalExpense extends Model
{
    use HasFactory;

    protected $table = 'tbl_medical_expenses';

    protected $fillable = [
        "id",
        "farmer_id",
        "expense_date",
        "doctor_visit_fees",
        "treatment_expenses",
        "vaccination_expenses",
        "deworming_expenses",
        "other1",
        "other2",
        "other3",
        "other4",
        "other5",
        "total_price",
        "date"
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }
}